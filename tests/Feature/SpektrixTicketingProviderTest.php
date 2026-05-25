<?php

declare(strict_types=1);

use App\Infrastructure\Ticketing\Spektrix\SpektrixTicketingProvider;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

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
