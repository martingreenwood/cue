<?php

declare(strict_types=1);

use App\Domains\Ticketing\Data\BookingHandoffRequestData;
use App\Domains\Ticketing\Enums\CustomerJourney;
use App\Infrastructure\Ticketing\Spektrix\SpektrixTicketingProvider;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('ticketing.providers.spektrix.customer_facing_base_url', null);
    config()->set('ticketing.providers.spektrix.custom_domain_confirmed', false);
});

test('it maps public Spektrix catalogue responses into domain data', function () {
    config()->set('ticketing.providers.spektrix.base_url', 'https://system.spektrix.com/apitesting/api/v3');

    Http::preventStrayRequests();
    Http::fake([
        'https://system.spektrix.com/apitesting/api/v3/events*' => Http::response([spektrixEventPayload()]),
        'https://system.spektrix.com/apitesting/api/v3/instances/*/price-list' => Http::response(spektrixPriceListPayload()),
        'https://system.spektrix.com/apitesting/api/v3/instances*' => Http::response([spektrixPerformancePayload()]),
    ]);

    $provider = new SpektrixTicketingProvider;
    $from = CarbonImmutable::parse('2026-05-25', 'UTC');
    $until = CarbonImmutable::parse('2027-05-25', 'UTC');

    $event = $provider->events($from, $until)->sole();
    $performance = $provider->performances($from, $until)->sole();
    $prices = $provider->performancePrices($performance->externalId);
    $standardFromPrice = $prices->get(1);

    expect($event->externalId)->toBe('70401ASVSQQQCRPSCNRQPCKDQQQTGLMGK')
        ->and($event->title)->toBe('Aldwych Theatre -> Integration Controls 01')
        ->and($event->firstPerformanceAt?->toIso8601String())->toBe('2026-06-28T19:00:00+00:00')
        ->and($performance->eventExternalId)->toBe($event->externalId)
        ->and($performance->externalPriceListId)->toBe('7801ATTBPVLSHPLVGKMJNMMDGBTHMPBTK')
        ->and($performance->startsAt->toIso8601String())->toBe('2026-06-28T19:00:00+00:00')
        ->and($prices)->toHaveCount(3)
        ->and($standardFromPrice?->amountMinor)->toBe(2000)
        ->and($standardFromPrice?->currency)->toBe('GBP')
        ->and($standardFromPrice?->isDynamicPricingEligible)->toBeTrue();

    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/events')
        && $request['instanceStart_from'] === '2026-05-25'
        && $request['instanceStart_to'] === '2027-05-25');
    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/instances')
        && ! str_contains($request->url(), '/price-list')
        && $request['startFrom'] === '2026-05-25'
        && $request['startTo'] === '2027-05-25');
    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/instances/112659AKSSQTLRRKDQBVSTLTJPSGVLTTN/price-list'));
});

test('it builds a booking handoff using a web performance identifier when available', function () {
    config()->set('ticketing.providers.spektrix.iframe_base_url', 'https://system.spektrix.com/apitesting');

    $handoff = (new SpektrixTicketingProvider)->bookingHandoff(new BookingHandoffRequestData(
        performanceExternalId: '112659AKSSQTLRRKDQBVSTLTJPSGVLTTN',
        webPerformanceId: 'web-instance-id',
    ));

    expect($handoff?->url)->toBe(
        'https://system.spektrix.com/apitesting/website/ChooseSeats.aspx?WebInstanceId=web-instance-id&resize=true',
    )->and($handoff?->embedScriptUrl)->toBe(
        'https://system.spektrix.com/apitesting/website/scripts/integrate.js',
    );
});

test('it uses a customer-facing custom domain for the full booking handoff surface', function () {
    config()->set('ticketing.providers.spektrix.customer_facing_base_url', 'https://tickets.newwolseytheatre.co.uk/wolsey');
    config()->set('ticketing.providers.spektrix.custom_domain_confirmed', true);
    config()->set('ticketing.providers.spektrix.iframe_base_url', 'https://system.spektrix.com/apitesting');

    $handoff = (new SpektrixTicketingProvider)->bookingHandoff(new BookingHandoffRequestData(
        performanceExternalId: '112659AKSSQTLRRKDQBVSTLTJPSGVLTTN',
        webPerformanceId: 'web-instance-id',
    ));

    expect($handoff?->url)->toBe(
        'https://tickets.newwolseytheatre.co.uk/wolsey/website/ChooseSeats.aspx?WebInstanceId=web-instance-id&resize=true',
    )->and($handoff?->embedScriptUrl)->toBe(
        'https://tickets.newwolseytheatre.co.uk/wolsey/website/scripts/integrate.js',
    );
});

test('it does not activate a configured custom domain before spektrix confirmation', function () {
    config()->set('ticketing.providers.spektrix.customer_facing_base_url', 'https://tickets.newwolseytheatre.co.uk/wolsey');
    config()->set('ticketing.providers.spektrix.custom_domain_confirmed', false);
    config()->set('ticketing.providers.spektrix.iframe_base_url', 'https://system.spektrix.com/apitesting');

    $handoff = (new SpektrixTicketingProvider)->bookingHandoff(new BookingHandoffRequestData(
        performanceExternalId: '112659AKSSQTLRRKDQBVSTLTJPSGVLTTN',
        webPerformanceId: null,
    ));

    expect($handoff?->url)->toStartWith('https://system.spektrix.com/apitesting/');
});

test('it does not activate a legacy custom-domain fallback before spektrix confirmation', function () {
    config()->set('ticketing.providers.spektrix.iframe_base_url', 'https://tickets.newwolseytheatre.co.uk/wolsey');

    $provider = new SpektrixTicketingProvider;
    $handoff = $provider->bookingHandoff(new BookingHandoffRequestData(
        performanceExternalId: '112659AKSSQTLRRKDQBVSTLTJPSGVLTTN',
        webPerformanceId: null,
    ));

    expect($handoff)->toBeNull()
        ->and($provider->customerSession())->toBeNull();
});

test('it does not activate a confirmed malformed custom domain', function () {
    config()->set('ticketing.providers.spektrix.customer_facing_base_url', 'http://tickets.newwolseytheatre.co.uk');
    config()->set('ticketing.providers.spektrix.custom_domain_confirmed', true);
    config()->set('ticketing.providers.spektrix.iframe_base_url', 'https://system.spektrix.com/apitesting');

    $provider = new SpektrixTicketingProvider;
    $handoff = $provider->bookingHandoff(new BookingHandoffRequestData(
        performanceExternalId: '112659AKSSQTLRRKDQBVSTLTJPSGVLTTN',
        webPerformanceId: null,
    ));

    expect($handoff?->url)->toStartWith('https://system.spektrix.com/apitesting/')
        ->and($provider->customerSession()?->clientName)->toBe('apitesting')
        ->and($provider->customerSession()?->customDomain)->toBeNull();
});

test('it configures customer session components from the active demo booking domain', function () {
    config()->set('ticketing.providers.spektrix.iframe_base_url', 'https://system.spektrix.com/apitesting');

    $customerSession = (new SpektrixTicketingProvider)->customerSession();

    expect($customerSession?->clientName)->toBe('apitesting')
        ->and($customerSession?->customDomain)->toBeNull()
        ->and($customerSession?->componentLoaderUrl)->toBe('https://webcomponents.spektrix.com/stable/spektrix-component-loader.js')
        ->and($customerSession?->customerUrl)->toBe('https://system.spektrix.com/apitesting/api/v3/customer')
        ->and($customerSession?->updateCustomerUrl)->toBe('https://system.spektrix.com/apitesting/api/v3/customer')
        ->and($customerSession?->statementsUrl)->toBe('https://system.spektrix.com/apitesting/api/v3/statements')
        ->and($customerSession?->agreedStatementsUrl)->toBe('https://system.spektrix.com/apitesting/api/v3/customer/agreed-statements')
        ->and($customerSession?->addressesUrl)->toBe('https://system.spektrix.com/apitesting/api/v3/customer/addresses')
        ->and($customerSession?->countriesUrl)->toBe('https://system.spektrix.com/apitesting/api/v3/countries')
        ->and($customerSession?->postcodeLookupUrl)->toBe('https://system.spektrix.com/apitesting/api/v3/postcode-lookup')
        ->and($customerSession?->ordersUrl)->toBe('https://system.spektrix.com/apitesting/api/v3/customer/orders')
        ->and($customerSession?->printAtHomeDocumentsUrl)->toBe('https://system.spektrix.com/apitesting/api/v3/print-at-home-documents')
        ->and($customerSession?->storedCardsUrl)->toBe('https://system.spektrix.com/apitesting/api/v3/customer/stored-cards')
        ->and($customerSession?->changePasswordUrl)->toBe('https://system.spektrix.com/apitesting/api/v3/customer/change-password')
        ->and($customerSession?->forgotPasswordUrl)->toBe('https://system.spektrix.com/apitesting/api/v3/customer/forgot-password')
        ->and($customerSession?->deauthenticateUrl)->toBe('https://system.spektrix.com/apitesting/api/v3/customer/deauthenticate');
});

test('it configures customer session components using only a confirmed custom domain', function () {
    config()->set('ticketing.providers.spektrix.customer_facing_base_url', 'https://tickets.newwolseytheatre.co.uk/wolsey');
    config()->set('ticketing.providers.spektrix.custom_domain_confirmed', true);
    config()->set('ticketing.providers.spektrix.iframe_base_url', 'https://system.spektrix.com/apitesting');

    $customerSession = (new SpektrixTicketingProvider)->customerSession();

    expect($customerSession?->clientName)->toBe('wolsey')
        ->and($customerSession?->customDomain)->toBe('tickets.newwolseytheatre.co.uk')
        ->and($customerSession?->customerUrl)->toBe('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer')
        ->and($customerSession?->updateCustomerUrl)->toBe('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer')
        ->and($customerSession?->statementsUrl)->toBe('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/statements')
        ->and($customerSession?->agreedStatementsUrl)->toBe('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/agreed-statements')
        ->and($customerSession?->addressesUrl)->toBe('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/addresses')
        ->and($customerSession?->countriesUrl)->toBe('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/countries')
        ->and($customerSession?->postcodeLookupUrl)->toBe('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/postcode-lookup')
        ->and($customerSession?->ordersUrl)->toBe('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/orders')
        ->and($customerSession?->printAtHomeDocumentsUrl)->toBe('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/print-at-home-documents')
        ->and($customerSession?->storedCardsUrl)->toBe('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/stored-cards')
        ->and($customerSession?->changePasswordUrl)->toBe('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/change-password')
        ->and($customerSession?->forgotPasswordUrl)->toBe('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/forgot-password')
        ->and($customerSession?->deauthenticateUrl)->toBe('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/deauthenticate');
});

test('it builds provider-isolated customer authentication and basket journey surfaces', function () {
    config()->set('ticketing.providers.spektrix.iframe_base_url', 'https://system.spektrix.com/apitesting');

    $provider = new SpektrixTicketingProvider;

    expect($provider->customerAuthentication()?->authenticateUrl)->toBe(
        'https://system.spektrix.com/apitesting/api/v3/customer/authenticate',
    )->and($provider->customerAuthentication()?->createCustomerUrl)->toBe(
        'https://system.spektrix.com/apitesting/api/v3/customer',
    )->and($provider->customerAuthentication()?->sendMagicLinkUrl)->toBe(
        'https://system.spektrix.com/apitesting/api/v3/customer/send-magic-link',
    )->and($provider->customerAuthentication()?->authenticateMagicLinkUrl)->toBe(
        'https://system.spektrix.com/apitesting/api/v3/customer/authenticate-magic-link',
    )->and($provider->customerJourney(CustomerJourney::PasswordReset)?->iframeUrl)->toBe(
        'https://system.spektrix.com/apitesting/website/Secure/SetPassword.aspx',
    )->and($provider->customerJourney(CustomerJourney::Redeem)?->iframeUrl)->toBe(
        'https://system.spektrix.com/apitesting/website/secure/RedeemGift.aspx',
    )->and($provider->customerJourney(CustomerJourney::Renew)?->iframeUrl)->toBe(
        'https://system.spektrix.com/apitesting/website/Memberships.aspx',
    );
});

test('it keeps customer authentication and journey surfaces on the confirmed custom domain', function () {
    config()->set('ticketing.providers.spektrix.customer_facing_base_url', 'https://tickets.newwolseytheatre.co.uk/wolsey');
    config()->set('ticketing.providers.spektrix.custom_domain_confirmed', true);
    config()->set('ticketing.providers.spektrix.iframe_base_url', 'https://system.spektrix.com/apitesting');

    $provider = new SpektrixTicketingProvider;

    expect($provider->customerAuthentication()?->authenticateUrl)->toBe(
        'https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/authenticate',
    )->and($provider->customerAuthentication()?->createCustomerUrl)->toBe(
        'https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer',
    )->and($provider->customerAuthentication()?->sendMagicLinkUrl)->toBe(
        'https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/send-magic-link',
    )->and($provider->customerJourney(CustomerJourney::PasswordReset)?->iframeUrl)->toBe(
        'https://tickets.newwolseytheatre.co.uk/wolsey/website/Secure/SetPassword.aspx',
    )->and($provider->customerJourney(CustomerJourney::Redeem)?->iframeUrl)->toBe(
        'https://tickets.newwolseytheatre.co.uk/wolsey/website/secure/RedeemGift.aspx',
    )->and($provider->customerJourney(CustomerJourney::Renew)?->iframeUrl)->toBe(
        'https://tickets.newwolseytheatre.co.uk/wolsey/website/Memberships.aspx',
    );
});

test('it builds a booking handoff using the numeric api performance identifier as a fallback', function () {
    config()->set('ticketing.providers.spektrix.iframe_base_url', 'https://system.spektrix.com/apitesting');

    $handoff = (new SpektrixTicketingProvider)->bookingHandoff(new BookingHandoffRequestData(
        performanceExternalId: '112659AKSSQTLRRKDQBVSTLTJPSGVLTTN',
        webPerformanceId: null,
    ));

    expect($handoff?->url)->toBe(
        'https://system.spektrix.com/apitesting/website/ChooseSeats.aspx?EventInstanceId=112659&resize=true',
    );
});

test('it does not expose an invalid booking handoff without a supported identifier', function () {
    config()->set('ticketing.providers.spektrix.iframe_base_url', 'https://system.spektrix.com/apitesting');

    $handoff = (new SpektrixTicketingProvider)->bookingHandoff(new BookingHandoffRequestData(
        performanceExternalId: 'invalid-instance-id',
        webPerformanceId: null,
    ));

    expect($handoff)->toBeNull();
});
