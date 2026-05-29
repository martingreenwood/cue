<?php

declare(strict_types=1);

use App\Domains\CMS\Models\PublicSiteSetting;
use App\Domains\Events\Enums\FilterGroup;
use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\EventEditorial;
use App\Domains\Events\Models\EventRedirect;
use App\Domains\Events\Models\FilterTerm;
use App\Domains\Events\Models\Performance;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'ticketing.pricing.currency' => 'GBP',
        'ticketing.pricing.stale_after_minutes' => 60,
        'ticketing.providers.spektrix.customer_facing_base_url' => null,
        'ticketing.providers.spektrix.custom_domain_confirmed' => false,
        'ticketing.providers.spektrix.iframe_base_url' => 'https://system.spektrix.com/apitesting',
        'ticketing.display_timezone' => 'Europe/London',
    ]);
});

test('the public listing includes published upcoming events using editorial presentation', function () {
    Http::preventStrayRequests();

    $publishedEvent = Event::factory()->create(['title' => 'Provider title']);
    EventEditorial::factory()->for($publishedEvent)->create([
        'title' => 'The Glass Menagerie',
        'slug' => 'the-glass-menagerie',
        'summary' => 'A memory play newly imagined.',
        'is_published' => true,
        'published_at' => now()->subMinute(),
    ]);
    Performance::factory()->for($publishedEvent)->create([
        'display_from_price_minor' => 2000,
        'display_currency' => 'GBP',
        'prices_synced_at' => now(),
    ]);

    $draftEvent = Event::factory()->create(['title' => 'Hidden rehearsal']);
    EventEditorial::factory()->for($draftEvent)->create(['is_published' => false]);

    $response = $this->get(route('events.index'));

    $response
        ->assertSuccessful()
        ->assertSee('The Glass Menagerie')
        ->assertSee('A memory play newly imagined.')
        ->assertSee('Guide price from £20.00')
        ->assertSee('Current ticket availability and final prices are confirmed during secure booking.')
        ->assertDontSee('Provider title')
        ->assertDontSee('Hidden rehearsal');
});

test('public pages expose Spektrix customer session status and basket count in a utility bar', function () {
    $this->get(route('events.index'))
        ->assertSuccessful()
        ->assertSee('https://webcomponents.spektrix.com/stable/spektrix-component-loader.js', false)
        ->assertSee('<spektrix-login-status', false)
        ->assertSee('<spektrix-basket-summary', false)
        ->assertSee('client-name="apitesting"', false)
        ->assertSee('class="!inline-flex min-h-11 items-center"', false)
        ->assertSee('href="'.route('ticketing.login').'"', false)
        ->assertSee('href="'.route('ticketing.account').'"', false)
        ->assertSee('href="'.route('ticketing.basket').'"', false)
        ->assertSee('data-customer-session-bar', false)
        ->assertSee('data-customer-url="https://system.spektrix.com/apitesting/api/v3/customer"', false)
        ->assertSee('data-customer-logout-button', false)
        ->assertSee('data-deauthenticate-url="https://system.spektrix.com/apitesting/api/v3/customer/deauthenticate"', false)
        ->assertSee('Log in')
        ->assertSee('Log out')
        ->assertSee('Basket')
        ->assertDontSee('Checking account')
        ->assertDontSee('Checking basket')
        ->assertDontSee('data-loading-container', false);
});

test('public account page hydrates from the provider current customer endpoint', function () {
    $this->get(route('ticketing.account'))
        ->assertSuccessful()
        ->assertSee('My account')
        ->assertSee('data-customer-account', false)
        ->assertSee('data-customer-url="https://system.spektrix.com/apitesting/api/v3/customer"', false)
        ->assertSee('data-update-customer-url="https://system.spektrix.com/apitesting/api/v3/customer"', false)
        ->assertSee('data-statements-url="https://system.spektrix.com/apitesting/api/v3/statements"', false)
        ->assertSee('data-agreed-statements-url="https://system.spektrix.com/apitesting/api/v3/customer/agreed-statements"', false)
        ->assertSee('data-addresses-url="https://system.spektrix.com/apitesting/api/v3/customer/addresses"', false)
        ->assertSee('data-countries-url="https://system.spektrix.com/apitesting/api/v3/countries"', false)
        ->assertSee('data-postcode-lookup-url="https://system.spektrix.com/apitesting/api/v3/postcode-lookup"', false)
        ->assertSee('data-orders-url="https://system.spektrix.com/apitesting/api/v3/customer/orders"', false)
        ->assertSee('data-print-at-home-documents-url="https://system.spektrix.com/apitesting/api/v3/print-at-home-documents"', false)
        ->assertSee('data-stored-cards-url="https://system.spektrix.com/apitesting/api/v3/customer/stored-cards"', false)
        ->assertSee('data-change-password-url="https://system.spektrix.com/apitesting/api/v3/customer/change-password"', false)
        ->assertSee('data-forgot-password-url="https://system.spektrix.com/apitesting/api/v3/customer/forgot-password"', false)
        ->assertSee('data-deauthenticate-url="https://system.spektrix.com/apitesting/api/v3/customer/deauthenticate"', false)
        ->assertSee('data-password-reset-requested-url="'.route('ticketing.login', ['password_reset' => 'requested']).'"', false)
        ->assertSee('data-account-profile-form', false)
        ->assertSee('data-account-profile-form hidden', false)
        ->assertSee('data-account-profile-summary', false)
        ->assertSee('data-account-profile-edit-button', false)
        ->assertSee('data-account-profile-cancel-button', false)
        ->assertSee('name="title"', false)
        ->assertSee('name="firstName"', false)
        ->assertSee('name="birthDate"', false)
        ->assertSee('name="giftAidConfirmed"', false)
        ->assertSee('Date of birth')
        ->assertSee('Gift Aid')
        ->assertSee('href="'.route('ticketing.account.profile').'"', false)
        ->assertSee('href="'.route('ticketing.account.addresses').'"', false)
        ->assertSee('href="'.route('ticketing.account.orders').'"', false)
        ->assertSee('href="'.route('ticketing.account.payments').'"', false)
        ->assertSee('href="'.route('ticketing.account.security').'"', false)
        ->assertSee('href="'.route('ticketing.account.contact-preferences').'"', false)
        ->assertSee('Profile')
        ->assertSee('Security')
        ->assertSee('Contact preferences')
        ->assertSee('Sign in to continue')
        ->assertDontSee('<iframe', false);

    $this->get(route('ticketing.account.addresses'))
        ->assertSuccessful()
        ->assertSee('Saved addresses')
        ->assertSee('data-account-address-form', false)
        ->assertSee('data-account-address-new-button', false)
        ->assertSee('data-account-address-postcode-button', false)
        ->assertSee('name="isBilling"', false)
        ->assertSee('name="isDelivery"', false)
        ->assertDontSee('data-account-profile-form', false);

    $this->get(route('ticketing.account.orders'))
        ->assertSuccessful()
        ->assertSee('Recent orders')
        ->assertSee('data-account-orders', false)
        ->assertSee('data-account-print-at-home-documents', false)
        ->assertSee('E-tickets');

    $this->get(route('ticketing.account.payments'))
        ->assertSuccessful()
        ->assertSee('Stored cards')
        ->assertSee('data-account-stored-cards', false)
        ->assertSee('data-account-stored-cards-include-pending', false)
        ->assertSee('Saved payment cards are managed securely by Spektrix');

    $this->get(route('ticketing.account.security'))
        ->assertSuccessful()
        ->assertSee('data-account-password-form', false)
        ->assertSee('name="oldPassword"', false)
        ->assertSee('Forgotten your current password?')
        ->assertSee('data-account-password-recovery-form', false)
        ->assertSee('data-domain="cue.test"', false)
        ->assertSee('name="emailAddress"', false)
        ->assertSee('https://system.spektrix.com/apitesting/api/v3/customer/forgot-password?domain=cue.test', false)
        ->assertSee('Email me a sign-in link instead');

    $this->get(route('ticketing.account.contact-preferences'))
        ->assertSuccessful()
        ->assertSee('data-account-contact-preferences-form', false)
        ->assertSee('Communication choices')
        ->assertSee('Save preferences')
        ->assertSee('data-statements-url="https://system.spektrix.com/apitesting/api/v3/statements"', false)
        ->assertSee('https://system.spektrix.com/apitesting/api/v3/customer/agreed-statements', false);
});

test('public customer session links open a direct provider login form and an embedded basket page', function () {
    $this->get(route('ticketing.login'))
        ->assertSuccessful()
        ->assertSee('Log in')
        ->assertSee('data-customer-login-page', false)
        ->assertSee('data-customer-url="https://system.spektrix.com/apitesting/api/v3/customer"', false)
        ->assertSee('data-account-url="'.route('ticketing.account').'"', false)
        ->assertSee('data-customer-login-form', false)
        ->assertSee('method="post"', false)
        ->assertSee('action="https://system.spektrix.com/apitesting/api/v3/customer/authenticate"', false)
        ->assertSee('https://system.spektrix.com/apitesting/api/v3/customer/authenticate', false)
        ->assertSee('href="'.route('ticketing.register').'"', false)
        ->assertSee('data-customer-magic-link-request-form', false)
        ->assertSee('https://system.spektrix.com/apitesting/api/v3/customer/send-magic-link', false)
        ->assertSee(route('ticketing.magic-link').'?token={token}', false)
        ->assertSee('data-customer-magic-link-error', false)
        ->assertSee('Forgotten your password?')
        ->assertSee('Email me a sign-in link')
        ->assertDontSee('<iframe', false);

    $this->get(route('ticketing.login', ['password_reset' => 'complete']))
        ->assertSuccessful()
        ->assertSee('Your password has been updated. You can now log in.');

    $this->get(route('ticketing.login', ['password_reset' => 'requested']))
        ->assertSuccessful()
        ->assertSee('Your reset link is on its way. We have signed you out so the link can open the secure password reset form.');

    $this->get(route('ticketing.login', ['account_created' => 'true']))
        ->assertSuccessful()
        ->assertSee('Your account has been created. Log in to continue.');

    $this->get(route('ticketing.basket'))
        ->assertSuccessful()
        ->assertSee('Your basket')
        ->assertSee('data-customer-basket', false)
        ->assertSee('data-basket-url="https://system.spektrix.com/apitesting/api/v3/basket"', false)
        ->assertSee('data-basket-tickets-url="https://system.spektrix.com/apitesting/api/v3/basket/tickets"', false)
        ->assertSee('data-stock-items-url="https://system.spektrix.com/apitesting/api/v3/stock-items"', false)
        ->assertSee('data-client-name="apitesting"', false)
        ->assertDontSee('<iframe', false);

    $this->get(route('ticketing.checkout'))
        ->assertSuccessful()
        ->assertSee('Checkout')
        ->assertSee('data-customer-checkout', false)
        ->assertSee('data-initiate-direct-payment-url="https://system.spektrix.com/apitesting/api/v3/basket/initiate-direct-payment"', false)
        ->assertSee('data-initiate-customer-payment-url="https://system.spektrix.com/apitesting/api/v3/basket/initiate-customer-payment"', false)
        ->assertSee('<spektrix-payments', false)
        ->assertSee('system-name="apitesting"', false)
        ->assertDontSee('<iframe', false);

    $this->get(route('ticketing.redeem'))
        ->assertSuccessful()
        ->assertSee('Redeem a gift voucher')
        ->assertSee('https://system.spektrix.com/apitesting/website/secure/RedeemGift.aspx', false)
        ->assertSee('<iframe', false);

    $this->get(route('ticketing.renew'))
        ->assertSuccessful()
        ->assertSee('Renew membership')
        ->assertSee('https://system.spektrix.com/apitesting/website/Memberships.aspx', false)
        ->assertSee('<iframe', false);

    $this->get(route('ticketing.blank'))
        ->assertSuccessful()
        ->assertSee('<body></body>', false)
        ->assertDontSee('Cue')
        ->assertDontSee('<iframe', false);
});

test('public account recovery exposes magic link access and retains the provider password-reset return destination', function () {
    $this->get(route('ticketing.password-reset'))
        ->assertSuccessful()
        ->assertSee('Reset password')
        ->assertSee('https://system.spektrix.com/apitesting/website/Secure/SetPassword.aspx', false)
        ->assertSee('<iframe', false);

    $this->get(route('ticketing.magic-link', ['token' => 'magic-token']))
        ->assertSuccessful()
        ->assertSee('Complete sign in')
        ->assertSee('data-customer-magic-link-authentication-form', false)
        ->assertSee('https://system.spektrix.com/apitesting/api/v3/customer/authenticate-magic-link', false)
        ->assertDontSee('<iframe', false);
});

test('public registration page creates a customer through the provider-isolated endpoint', function () {
    $this->get(route('ticketing.register'))
        ->assertSuccessful()
        ->assertSee('Create an account')
        ->assertSee('data-customer-registration-form', false)
        ->assertSee('data-domain="cue.test"', false)
        ->assertSee('action="https://system.spektrix.com/apitesting/api/v3/customer?domain=cue.test"', false)
        ->assertSee('https://system.spektrix.com/apitesting/api/v3/customer', false)
        ->assertSee('name="firstName"', false)
        ->assertSee('name="lastName"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="password_confirmation"', false)
        ->assertDontSee('<iframe', false);
});

test('public customer session controls use the confirmed custom-domain host only', function () {
    config([
        'ticketing.providers.spektrix.customer_facing_base_url' => 'https://tickets.newwolseytheatre.co.uk/wolsey',
        'ticketing.providers.spektrix.custom_domain_confirmed' => true,
    ]);

    $this->get(route('events.index'))
        ->assertSuccessful()
        ->assertSee('client-name="wolsey"', false)
        ->assertSee('custom-domain="tickets.newwolseytheatre.co.uk"', false)
        ->assertDontSee('client-name="apitesting"', false);

    $this->get(route('ticketing.login'))
        ->assertSuccessful()
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/authenticate', false)
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/send-magic-link', false)
        ->assertDontSee('<iframe', false);

    $this->get(route('ticketing.register'))
        ->assertSuccessful()
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer', false);

    $this->get(route('ticketing.account'))
        ->assertSuccessful()
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer', false)
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/statements', false)
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/addresses', false)
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/countries', false)
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/postcode-lookup', false)
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/orders', false)
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/print-at-home-documents', false)
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/stored-cards', false)
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/change-password', false)
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/forgot-password', false)
        ->assertDontSee('<iframe', false);

    $this->get(route('ticketing.password-reset'))
        ->assertSuccessful()
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/website/Secure/SetPassword.aspx', false);

    $this->get(route('ticketing.magic-link', ['token' => 'magic-token']))
        ->assertSuccessful()
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/customer/authenticate-magic-link', false);

    $this->get(route('ticketing.basket'))
        ->assertSuccessful()
        ->assertSee('data-basket-url="https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/basket"', false)
        ->assertSee('data-client-name="wolsey"', false)
        ->assertDontSee('<iframe', false);

    $this->get(route('ticketing.checkout'))
        ->assertSuccessful()
        ->assertSee('data-initiate-direct-payment-url="https://tickets.newwolseytheatre.co.uk/wolsey/api/v3/basket/initiate-direct-payment"', false)
        ->assertSee('system-name="wolsey"', false)
        ->assertDontSee('<iframe', false);

    $this->get(route('ticketing.redeem'))
        ->assertSuccessful()
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/website/secure/RedeemGift.aspx', false);

    $this->get(route('ticketing.renew'))
        ->assertSuccessful()
        ->assertSee('https://tickets.newwolseytheatre.co.uk/wolsey/website/Memberships.aspx', false);
});

test('public availability and booking wording may be customised through site settings', function () {
    PublicSiteSetting::factory()->create([
        'id' => 1,
        'listing_kicker' => 'Tickets available',
        'guide_price_label' => 'Indicative price',
        'guide_price_prefix' => 'Seats from',
        'prices_confirmed_in_booking' => 'View seat pricing',
        'dynamic_price_suffix' => 'Prices vary',
        'stale_price_suffix' => 'Check now',
        'performance_freshness_notice' => 'Availability is confirmed on the next step.',
        'booking_cta_label' => 'Find seats',
        'online_booking_unavailable_label' => 'Call the box office',
        'secure_booking_prefix' => 'Book safely for',
        'footer_availability_notice' => 'Live ticket details appear during booking.',
        'customer_logged_in_label' => 'Welcome',
        'customer_logged_out_label' => 'Account login unavailable',
        'customer_basket_label' => 'Your basket',
    ]);

    $event = Event::factory()->create(['slug' => 'editable-copy']);
    EventEditorial::factory()->for($event)->create([
        'slug' => 'editable-copy',
        'title' => 'Editable Copy Event',
        'is_published' => true,
    ]);
    Performance::factory()->for($event)->create([
        'external_id' => '112659AKSSQTLRRKDQBVSTLTJPSGVLTTN',
        'is_on_sale' => true,
        'display_from_price_minor' => 2000,
        'display_currency' => 'GBP',
        'has_dynamic_pricing' => true,
        'prices_synced_at' => now()->subHours(2),
    ]);

    $this->get(route('events.index'))
        ->assertSuccessful()
        ->assertSee('Tickets available')
        ->assertSee('Seats from £20.00')
        ->assertSee('Live ticket details appear during booking.')
        ->assertSee('Account login unavailable')
        ->assertSee('Your basket');

    $this->get(route('events.show', ['slug' => 'editable-copy']))
        ->assertSuccessful()
        ->assertSee('Indicative price')
        ->assertSee('Availability is confirmed on the next step.')
        ->assertSee('Seats from £20.00')
        ->assertSee('Prices vary')
        ->assertSee('Check now')
        ->assertSee('Find seats');
});

test('linked event artwork is named and subsequent listing images defer loading', function () {
    $firstEvent = Event::factory()->create([
        'title' => 'First source title',
        'local_image_path' => 'events/source/first/hero.jpg',
        'first_performance_at' => now()->addDay(),
        'last_performance_at' => now()->addDays(2),
    ]);
    EventEditorial::factory()->for($firstEvent)->create([
        'title' => 'First Published Event',
        'slug' => 'first-published-event',
        'is_published' => true,
    ]);

    $secondEvent = Event::factory()->create([
        'title' => 'Second source title',
        'local_image_path' => 'events/source/second/hero.jpg',
        'first_performance_at' => now()->addDays(3),
        'last_performance_at' => now()->addDays(4),
    ]);
    EventEditorial::factory()->for($secondEvent)->create([
        'title' => 'Second Published Event',
        'slug' => 'second-published-event',
        'is_published' => true,
    ]);

    $this->get(route('events.index'))
        ->assertSuccessful()
        ->assertSee('aria-label="View details for First Published Event"', false)
        ->assertSee('aria-label="View details for Second Published Event"', false)
        ->assertSee('loading="eager" fetchpriority="high" decoding="async"', false)
        ->assertSee('loading="lazy" fetchpriority="low" decoding="async"', false);
});

test('public event search uses editorial content without exposing replaced provider content', function () {
    $event = Event::factory()->create([
        'title' => 'Private Provider Hamlet Label',
        'summary' => 'Provider-only synopsis.',
    ]);
    EventEditorial::factory()->for($event)->create([
        'title' => 'Hamlet',
        'summary' => 'A royal tragedy.',
        'is_published' => true,
    ]);

    $this->get(route('events.index', ['q' => 'royal tragedy']))
        ->assertSuccessful()
        ->assertSee('Hamlet')
        ->assertSee('A royal tragedy.');

    $this->get(route('events.index', ['q' => 'Private Provider']))
        ->assertSuccessful()
        ->assertSee('No events match your search.')
        ->assertDontSee('Hamlet');
});

test('public event search includes provider content when there is no editorial override', function () {
    $event = Event::factory()->create([
        'title' => 'Source Only Production',
        'summary' => 'An imported family adventure.',
    ]);
    EventEditorial::factory()->for($event)->create([
        'title' => null,
        'summary' => null,
        'is_published' => true,
    ]);

    $this->get(route('events.index', ['q' => 'family adventure']))
        ->assertSuccessful()
        ->assertSee('Source Only Production')
        ->assertSee('An imported family adventure.');
});

test('the public listing filters upcoming events by date window without hiding unsold events', function () {
    $soonOnSale = Event::factory()->create([
        'title' => 'Soon On Sale',
        'is_on_sale' => true,
        'first_performance_at' => now()->addDays(10),
        'last_performance_at' => now()->addDays(12),
    ]);
    EventEditorial::factory()->for($soonOnSale)->create([
        'title' => null,
        'is_published' => true,
    ]);

    $laterOnSale = Event::factory()->create([
        'title' => 'Later On Sale',
        'is_on_sale' => true,
        'first_performance_at' => now()->addDays(45),
        'last_performance_at' => now()->addDays(48),
    ]);
    EventEditorial::factory()->for($laterOnSale)->create([
        'title' => null,
        'is_published' => true,
    ]);

    $soonNotOnSale = Event::factory()->create([
        'title' => 'Soon Not On Sale',
        'is_on_sale' => false,
        'first_performance_at' => now()->addDays(7),
        'last_performance_at' => now()->addDays(8),
    ]);
    EventEditorial::factory()->for($soonNotOnSale)->create([
        'title' => null,
        'is_published' => true,
    ]);

    $this->get(route('events.index', [
        'when' => 'next-30-days',
    ]))
        ->assertSuccessful()
        ->assertSee('Soon On Sale')
        ->assertSee('Soon Not On Sale')
        ->assertSee('2 events found')
        ->assertSee('value="next-30-days" selected', false)
        ->assertDontSee('Later On Sale');
});

test('the public listing combines editorial what offers and performance access filters', function () {
    $drama = FilterTerm::factory()->create([
        'filter_group' => FilterGroup::What,
        'name' => 'Drama',
        'slug' => 'drama',
    ]);
    $comedy = FilterTerm::factory()->create([
        'filter_group' => FilterGroup::What,
        'name' => 'Comedy',
        'slug' => 'comedy',
    ]);
    $members = FilterTerm::factory()->create([
        'filter_group' => FilterGroup::Offers,
        'name' => 'Members',
        'slug' => 'members',
    ]);
    $captioned = FilterTerm::factory()->create([
        'filter_group' => FilterGroup::Access,
        'name' => 'Captioned',
        'slug' => 'captioned',
    ]);

    $dramaEvent = Event::factory()->create(['title' => 'Captioned Drama']);
    EventEditorial::factory()->for($dramaEvent)->create([
        'title' => 'Captioned Drama',
        'is_published' => true,
    ]);
    $dramaEvent->whatTerms()->attach($drama);
    $dramaEvent->offerTerms()->attach($members);
    $dramaPerformance = Performance::factory()->for($dramaEvent)->create(['starts_at' => now()->addWeek()]);
    $dramaPerformance->accessTerms()->attach($captioned);

    $comedyEvent = Event::factory()->create(['title' => 'Captioned Comedy']);
    EventEditorial::factory()->for($comedyEvent)->create([
        'title' => 'Captioned Comedy',
        'is_published' => true,
    ]);
    $comedyEvent->whatTerms()->attach($comedy);
    $comedyEvent->offerTerms()->attach($members);
    $comedyPerformance = Performance::factory()->for($comedyEvent)->create(['starts_at' => now()->addWeek()]);
    $comedyPerformance->accessTerms()->attach($captioned);

    $withoutOffer = Event::factory()->create(['title' => 'No Members Offer']);
    EventEditorial::factory()->for($withoutOffer)->create([
        'title' => 'No Members Offer',
        'is_published' => true,
    ]);
    $withoutOffer->whatTerms()->attach($drama);
    $withoutOfferPerformance = Performance::factory()->for($withoutOffer)->create(['starts_at' => now()->addWeek()]);
    $withoutOfferPerformance->accessTerms()->attach($captioned);

    $pastAccessOnly = Event::factory()->create(['title' => 'Past Captioned Only']);
    EventEditorial::factory()->for($pastAccessOnly)->create([
        'title' => 'Past Captioned Only',
        'is_published' => true,
    ]);
    $pastAccessOnly->whatTerms()->attach($drama);
    $pastAccessOnly->offerTerms()->attach($members);
    $pastPerformance = Performance::factory()->for($pastAccessOnly)->create(['starts_at' => now()->subWeek()]);
    $pastPerformance->accessTerms()->attach($captioned);
    Performance::factory()->for($pastAccessOnly)->create(['starts_at' => now()->addWeek()]);

    $this->get(route('events.index', [
        'what' => ['drama', 'comedy'],
        'offers' => ['members'],
        'access' => ['captioned'],
    ]))
        ->assertSuccessful()
        ->assertSee('Captioned Drama')
        ->assertSee('Captioned Comedy')
        ->assertSee('2 events found')
        ->assertSee('name="what[]" value="drama" checked', false)
        ->assertSee('name="offers[]" value="members" checked', false)
        ->assertSee('name="access[]" value="captioned" checked', false)
        ->assertDontSee('No Members Offer')
        ->assertDontSee('Past Captioned Only');
});

test('public taxonomy filter values must exist in their intended filter group', function () {
    FilterTerm::factory()->create([
        'filter_group' => FilterGroup::What,
        'name' => 'Drama',
        'slug' => 'drama',
    ]);

    $this->get(route('events.index', ['access' => ['drama']]))
        ->assertSessionHasErrors('access.0');
});

test('an access filter explains when only past tagged performances exist', function () {
    $audioDescribed = FilterTerm::factory()->create([
        'filter_group' => FilterGroup::Access,
        'name' => 'Audio Described',
        'slug' => 'ad',
    ]);

    $event = Event::factory()->create(['title' => 'Past Accessible Performance']);
    EventEditorial::factory()->for($event)->create([
        'title' => 'Past Accessible Performance',
        'is_published' => true,
    ]);

    $pastPerformance = Performance::factory()->for($event)->create(['starts_at' => now()->subDay()]);
    $pastPerformance->accessTerms()->attach($audioDescribed);
    Performance::factory()->for($event)->create(['starts_at' => now()->addDay()]);

    $this->get(route('events.index', ['access' => ['ad']]))
        ->assertSuccessful()
        ->assertSee('No upcoming performances match your selected access filters.')
        ->assertSee('Access provisions are applied to individual performance dates.')
        ->assertDontSee('Past Accessible Performance');
});

test('scheduled events are hidden until their publication time', function () {
    $event = Event::factory()->create(['title' => 'Tomorrow announcement']);
    EventEditorial::factory()->for($event)->create([
        'is_published' => true,
        'published_at' => now()->addHour(),
    ]);

    $this->get(route('events.index'))
        ->assertSuccessful()
        ->assertDontSee('Tomorrow announcement');
});

test('the empty catalogue explains that no events are published', function () {
    $this->get(route('events.index'))
        ->assertSuccessful()
        ->assertSee('No events are currently published.')
        ->assertDontSee('No events are currently on sale.');
});

test('a public event page renders local editorial content and price guidance without api requests', function () {
    Http::preventStrayRequests();

    $event = Event::factory()->create([
        'title' => 'Provider title',
        'local_image_path' => 'events/source/12/hero.jpg',
    ]);
    EventEditorial::factory()->for($event)->create([
        'title' => 'A Streetcar Named Desire',
        'slug' => 'streetcar',
        'summary' => 'An urgent new staging.',
        'description_html' => '<p>Production synopsis.</p><script>not executable</script>',
        'seo_title' => 'A Streetcar Named Desire | Tickets',
        'seo_description' => 'Book A Streetcar Named Desire.',
        'is_published' => true,
    ]);
    $audioDescribed = FilterTerm::factory()->create([
        'filter_group' => FilterGroup::Access,
        'name' => 'Audio Described',
        'slug' => 'ad',
    ]);
    $performance = Performance::factory()->for($event)->create([
        'external_id' => '112659AKSSQTLRRKDQBVSTLTJPSGVLTTN',
        'starts_at' => now()->addWeek(),
        'display_from_price_minor' => 2500,
        'display_currency' => 'GBP',
        'has_dynamic_pricing' => true,
        'prices_synced_at' => now()->subHours(2),
    ]);
    $performance->accessTerms()->attach($audioDescribed);

    $response = $this->get(route('events.show', ['slug' => 'streetcar']));

    $response
        ->assertSuccessful()
        ->assertSee('A Streetcar Named Desire')
        ->assertSee('Production synopsis.')
        ->assertSee('Guide price from £25.00')
        ->assertSee('May change')
        ->assertSee('Check in booking')
        ->assertSee('Performance dates and guide prices are shown from our latest catalogue update.')
        ->assertSee('Check current availability and final prices during secure booking.')
        ->assertSee('Check availability and book')
        ->assertSee('Access provisions')
        ->assertSee('Audio Described')
        ->assertSee(route('events.show', ['slug' => 'streetcar', 'performance' => $performance->getKey()]).'#booking', false)
        ->assertDontSee('<iframe', false)
        ->assertSee('storage/events/source/12/hero.jpg')
        ->assertSee('A Streetcar Named Desire | Tickets')
        ->assertDontSee('<script>', false);
});

test('a long performance run initially presents a shorter list with an accessible disclosure', function () {
    $event = Event::factory()->create(['slug' => 'long-run']);
    EventEditorial::factory()->for($event)->create([
        'slug' => 'long-run',
        'title' => 'Long Running Event',
        'is_published' => true,
    ]);

    foreach (range(1, 10) as $performanceIndex) {
        Performance::factory()->for($event)->create([
            'starts_at' => now()->addDays($performanceIndex),
        ]);
    }

    $this->get(route('events.show', ['slug' => 'long-run']))
        ->assertSuccessful()
        ->assertSee('<details', false)
        ->assertSee('View 2 more performances')
        ->assertSee('<summary', false);
});

test('an event page filters performances by date windows exact dates and access provision', function () {
    $this->travelTo(CarbonImmutable::parse('2026-05-27 10:00:00', 'Europe/London'));

    $event = Event::factory()->create(['slug' => 'filtered-run']);
    EventEditorial::factory()->for($event)->create([
        'slug' => 'filtered-run',
        'title' => 'Filtered Run',
        'is_published' => true,
    ]);
    $audioDescribed = FilterTerm::factory()->create([
        'filter_group' => FilterGroup::Access,
        'name' => 'Audio Described',
        'slug' => 'ad',
    ]);
    $captioned = FilterTerm::factory()->create([
        'filter_group' => FilterGroup::Access,
        'name' => 'Captioned',
        'slug' => 'captioned',
    ]);

    $mayPerformance = Performance::factory()->for($event)->create([
        'starts_at' => CarbonImmutable::parse('2026-05-29 14:00:00', 'Europe/London'),
    ]);
    $mayPerformance->accessTerms()->attach($captioned);

    $audioDescribedPerformance = Performance::factory()->for($event)->create([
        'starts_at' => CarbonImmutable::parse('2026-06-03 14:00:00', 'Europe/London'),
    ]);
    $audioDescribedPerformance->accessTerms()->attach($audioDescribed);

    Performance::factory()->for($event)->create([
        'starts_at' => CarbonImmutable::parse('2026-06-04 14:00:00', 'Europe/London'),
    ]);

    $this->get(route('events.show', ['slug' => 'filtered-run', 'when' => 'this-month']))
        ->assertSuccessful()
        ->assertSee('Friday, 2:00pm')
        ->assertDontSee('Wednesday, 2:00pm')
        ->assertSee('value="this-month" selected', false);

    $this->get(route('events.show', ['slug' => 'filtered-run', 'date' => '2026-06-04']))
        ->assertSuccessful()
        ->assertSee('Thursday, 2:00pm')
        ->assertDontSee('Wednesday, 2:00pm')
        ->assertSee('value="2026-06-04"', false);

    $this->get(route('events.show', ['slug' => 'filtered-run', 'access' => ['ad']]))
        ->assertSuccessful()
        ->assertSee('Wednesday, 2:00pm')
        ->assertSee('Audio Described')
        ->assertDontSee('Friday, 2:00pm')
        ->assertDontSee('Thursday, 2:00pm')
        ->assertSee('name="access[]" value="ad" checked', false);
});

test('event performance access filters only accept access taxonomy terms', function () {
    $event = Event::factory()->create(['slug' => 'access-validation']);
    EventEditorial::factory()->for($event)->create([
        'slug' => 'access-validation',
        'is_published' => true,
    ]);
    FilterTerm::factory()->create([
        'filter_group' => FilterGroup::What,
        'name' => 'Drama',
        'slug' => 'drama',
    ]);

    $this->get(route('events.show', ['slug' => 'access-validation', 'access' => ['drama']]))
        ->assertSessionHasErrors('access.0');
});

test('selecting an available performance renders its Spektrix booking iframe in the event page', function () {
    Http::preventStrayRequests();

    $event = Event::factory()->create(['slug' => 'cabaret']);
    EventEditorial::factory()->for($event)->create([
        'slug' => 'cabaret',
        'title' => 'Cabaret',
        'is_published' => true,
    ]);
    $performance = Performance::factory()->for($event)->create([
        'external_id' => '112659AKSSQTLRRKDQBVSTLTJPSGVLTTN',
        'starts_at' => CarbonImmutable::parse('2026-05-29T18:00:00Z'),
    ]);

    $this->get(route('events.show', [
        'slug' => 'cabaret',
        'performance' => $performance->getKey(),
    ]))
        ->assertSuccessful()
        ->assertSee('Secure booking')
        ->assertSee('Check current availability and final prices securely with Spektrix for Friday 29 May 2026 at 7:00pm.')
        ->assertSee('id="SpektrixIFrame"', false)
        ->assertSee('name="SpektrixIFrame"', false)
        ->assertSee('https://system.spektrix.com/apitesting/website/ChooseSeats.aspx?EventInstanceId=112659&amp;resize=true', false)
        ->assertSee('https://system.spektrix.com/apitesting/website/scripts/integrate.js', false);
});

test('a performance without a supported handoff identifier does not render a broken booking link', function () {
    $event = Event::factory()->create(['slug' => 'no-booking-link']);
    EventEditorial::factory()->for($event)->create([
        'slug' => 'no-booking-link',
        'is_published' => true,
    ]);
    $performance = Performance::factory()->for($event)->create([
        'external_id' => 'non-numeric-provider-identifier',
        'is_on_sale' => true,
    ]);

    $this->get(route('events.show', [
        'slug' => 'no-booking-link',
        'performance' => $performance->getKey(),
    ]))
        ->assertSuccessful()
        ->assertSee('Online booking unavailable')
        ->assertDontSee('Check availability and book')
        ->assertDontSee('<iframe', false);
});

test('a published event falls back to source presentation when overrides are empty', function () {
    $event = Event::factory()->create([
        'slug' => 'provider-slug',
        'title' => 'Source title',
        'summary' => 'Source summary',
    ]);
    EventEditorial::factory()->for($event)->create([
        'title' => null,
        'slug' => null,
        'summary' => null,
        'description_html' => null,
        'is_published' => true,
    ]);

    $this->get(route('events.show', ['slug' => 'provider-slug']))
        ->assertSuccessful()
        ->assertSee('Source title')
        ->assertSee('Source summary');
});

test('draft events are not accessible publicly', function () {
    $event = Event::factory()->create(['slug' => 'draft-event']);
    EventEditorial::factory()->for($event)->create([
        'slug' => 'draft-event',
        'is_published' => false,
    ]);

    $this->get(route('events.show', ['slug' => 'draft-event']))
        ->assertNotFound();
});

test('active editorial redirects resolve obsolete public slugs', function () {
    $event = Event::factory()->create();
    EventEditorial::factory()->for($event)->create([
        'slug' => 'new-slug',
        'is_published' => true,
    ]);
    EventRedirect::factory()->for($event)->create([
        'source_path' => '/events/old-slug',
        'destination_path' => '/events/new-slug',
        'status_code' => 301,
        'is_active' => true,
    ]);

    $this->get('/events/old-slug')
        ->assertRedirect('/events/new-slug')
        ->assertStatus(301);
});
