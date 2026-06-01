<?php

declare(strict_types=1);

use App\Domains\CMS\Models\DonationFund;
use App\Domains\CMS\Models\Membership;
use App\Domains\Events\Enums\SyncRunStatus;
use App\Domains\Events\Models\AvailabilitySnapshot;
use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\Performance;
use App\Domains\Events\Models\SyncRun;
use App\Domains\Ticketing\Contracts\TicketingProvider;
use App\Filament\Widgets\AvailabilitySyncHealthWidget;
use App\Filament\Widgets\CatalogueHealthWidget;
use App\Filament\Widgets\JourneySyncHealthWidget;
use App\Filament\Widgets\PricingSyncHealthWidget;
use App\Filament\Widgets\SpektrixBookingDomainHealthWidget;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

// --- CatalogueHealthWidget ---

test('catalogue health widget shows event and performance counts', function () {
    $events = Event::factory()->count(3)->create();
    Performance::factory()->count(7)->for($events->first())->create();

    $stats = Livewire::test(CatalogueHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[0]->getValue())->toBe('3')
        ->and($stats[0]->getDescription())->toBe('7 performances');
});

test('catalogue health widget shows warning when no catalogue sync has run', function () {
    $stats = Livewire::test(CatalogueHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[1]->getValue())->toBe('Never synced')
        ->and($stats[1]->getColor())->toBe('warning');
});

test('catalogue health widget shows last successful catalogue sync', function () {
    SyncRun::factory()->create([
        'operation' => 'catalogue',
        'status' => SyncRunStatus::Succeeded,
        'events_synced' => 42,
        'performances_synced' => 729,
        'finished_at' => now()->subMinutes(5),
    ]);

    $stats = Livewire::test(CatalogueHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[1]->getColor())->toBe('success')
        ->and($stats[1]->getDescription())->toContain('42 events')
        ->and($stats[1]->getDescription())->toContain('729 performances');
});

test('catalogue health widget counts recent catalogue failures within 7 days', function () {
    SyncRun::factory()->count(2)->create([
        'operation' => 'catalogue',
        'status' => SyncRunStatus::Failed,
        'created_at' => now()->subDays(3),
    ]);

    // Older failure outside 7-day window should not count.
    SyncRun::factory()->create([
        'operation' => 'catalogue',
        'status' => SyncRunStatus::Failed,
        'created_at' => now()->subDays(10),
    ]);

    $stats = Livewire::test(CatalogueHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[2]->getValue())->toBe('2')
        ->and($stats[2]->getColor())->toBe('danger');
});

test('catalogue health widget shows success when no recent failures', function () {
    $stats = Livewire::test(CatalogueHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[2]->getValue())->toBe('0')
        ->and($stats[2]->getColor())->toBe('success');
});

// --- PricingSyncHealthWidget ---

test('pricing health widget shows priced and total future on-sale performances', function () {
    // Priced future on-sale performance.
    Performance::factory()->create([
        'is_on_sale' => true,
        'is_cancelled' => false,
        'starts_at' => now()->addDays(10),
        'display_from_price_minor' => 2000,
        'prices_synced_at' => now()->subMinutes(5),
    ]);

    // Future on-sale but unpriced.
    Performance::factory()->create([
        'is_on_sale' => true,
        'is_cancelled' => false,
        'starts_at' => now()->addDays(20),
        'display_from_price_minor' => null,
        'prices_synced_at' => null,
    ]);

    // Past performance - excluded from totals.
    Performance::factory()->create([
        'is_on_sale' => true,
        'is_cancelled' => false,
        'starts_at' => now()->subDay(),
        'display_from_price_minor' => 1500,
        'prices_synced_at' => now()->subMinutes(5),
    ]);

    $stats = Livewire::test(PricingSyncHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[0]->getValue())->toBe('1 / 2');
});

test('pricing health widget identifies stale and unpriced performances', function () {
    config(['ticketing.pricing.stale_after_minutes' => 60]);

    // Fresh pricing.
    Performance::factory()->create([
        'is_on_sale' => true,
        'is_cancelled' => false,
        'starts_at' => now()->addDays(10),
        'prices_synced_at' => now()->subMinutes(30),
    ]);

    // Stale pricing.
    Performance::factory()->create([
        'is_on_sale' => true,
        'is_cancelled' => false,
        'starts_at' => now()->addDays(20),
        'prices_synced_at' => now()->subMinutes(90),
    ]);

    // Unpriced.
    Performance::factory()->create([
        'is_on_sale' => true,
        'is_cancelled' => false,
        'starts_at' => now()->addDays(30),
        'prices_synced_at' => null,
    ]);

    $stats = Livewire::test(PricingSyncHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[1]->getValue())->toBe('2')
        ->and($stats[1]->getColor())->toBe('warning');
});

test('pricing health widget is green when all future performances have fresh pricing', function () {
    config(['ticketing.pricing.stale_after_minutes' => 60]);

    Performance::factory()->count(3)->create([
        'is_on_sale' => true,
        'is_cancelled' => false,
        'starts_at' => now()->addDays(10),
        'prices_synced_at' => now()->subMinutes(10),
    ]);

    $stats = Livewire::test(PricingSyncHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[1]->getValue())->toBe('0')
        ->and($stats[1]->getColor())->toBe('success');
});

test('pricing health widget shows warning when no price sync has run', function () {
    $stats = Livewire::test(PricingSyncHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[2]->getValue())->toBe('Never synced')
        ->and($stats[2]->getColor())->toBe('warning');
});

test('pricing health widget shows danger when recent price sync failures exist', function () {
    SyncRun::factory()->count(3)->create([
        'operation' => 'performance-prices',
        'status' => SyncRunStatus::Failed,
        'created_at' => now()->subDays(2),
        'finished_at' => now()->subDays(2),
    ]);

    $stats = Livewire::test(PricingSyncHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[2]->getColor())->toBe('danger')
        ->and($stats[2]->getDescription())->toContain('3 failure(s)');
});

test('pricing health widget excludes cancelled and off-sale performances from stale count', function () {
    config(['ticketing.pricing.stale_after_minutes' => 60]);

    // Cancelled - should not count.
    Performance::factory()->create([
        'is_on_sale' => true,
        'is_cancelled' => true,
        'starts_at' => now()->addDays(10),
        'prices_synced_at' => null,
    ]);

    // Off-sale - should not count.
    Performance::factory()->create([
        'is_on_sale' => false,
        'is_cancelled' => false,
        'starts_at' => now()->addDays(10),
        'prices_synced_at' => null,
    ]);

    $stats = Livewire::test(PricingSyncHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[1]->getValue())->toBe('0')
        ->and($stats[1]->getColor())->toBe('success');
});

test('booking domain widget warns while the system spektrix domain is customer-facing', function () {
    config([
        'ticketing.providers.spektrix.customer_facing_base_url' => 'https://system.spektrix.com/apitesting',
        'ticketing.providers.spektrix.custom_domain_confirmed' => false,
    ]);

    $stats = Livewire::test(SpektrixBookingDomainHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[0]->getValue())->toBe('system.spektrix.com')
        ->and($stats[0]->getColor())->toBe('warning')
        ->and($stats[0]->getDescription())->toContain('custom domain required');
});

test('booking domain widget confirms a customer-facing spektrix custom domain cutover', function () {
    config([
        'ticketing.providers.spektrix.customer_facing_base_url' => 'https://tickets.newwolseytheatre.co.uk/wolsey',
        'ticketing.providers.spektrix.custom_domain_confirmed' => true,
    ]);

    $stats = Livewire::test(SpektrixBookingDomainHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[0]->getValue())->toBe('tickets.newwolseytheatre.co.uk')
        ->and($stats[0]->getColor())->toBe('success')
        ->and($stats[0]->getDescription())->toContain('iframe and integrate.js');
});

test('booking domain widget rejects a custom host without a secure spektrix client path', function () {
    config([
        'ticketing.providers.spektrix.customer_facing_base_url' => 'http://tickets.newwolseytheatre.co.uk',
        'ticketing.providers.spektrix.custom_domain_confirmed' => true,
    ]);

    $stats = Livewire::test(SpektrixBookingDomainHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[0]->getColor())->toBe('danger')
        ->and($stats[0]->getDescription())->toContain('HTTPS custom URL')
        ->and($stats[0]->getDescription())->toContain('client name path');
});

test('availability sync health widget shows warning when no snapshot exists', function () {
    $stats = Livewire::test(AvailabilitySyncHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[0]->getValue())->toBe('No snapshot yet')
        ->and($stats[0]->getColor())->toBe('warning');
});

test('availability sync health widget shows stale and unpriced quality concerns', function () {
    AvailabilitySnapshot::factory()->create([
        'future_on_sale_total' => 12,
        'future_on_sale_available' => 9,
        'future_on_sale_stale' => 3,
        'future_on_sale_unpriced' => 1,
        'captured_at' => now()->subMinutes(5),
    ]);

    $stats = Livewire::test(AvailabilitySyncHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[0]->getValue())->toBe('9 / 12')
        ->and($stats[1]->getValue())->toBe('3 stale, 1 unpriced')
        ->and($stats[1]->getColor())->toBe('warning')
        ->and($stats[2]->getColor())->toBe('success');
});

test('journey sync health widget shows stale freshness when journey sync is overdue', function () {
    config(['ticketing.journeys.stale_after_minutes' => 180]);

    DonationFund::factory()->create([
        'provider' => 'spektrix',
        'external_id' => 'fund-1',
        'synced_at' => now()->subHours(6),
        'is_visible' => true,
    ]);

    Membership::factory()->create([
        'provider' => 'spektrix',
        'external_id' => 'membership-1',
        'synced_at' => now()->subHours(6),
        'is_visible' => true,
    ]);

    $provider = mock(TicketingProvider::class);
    $provider->shouldReceive('providerKey')->andReturn('spektrix');
    $provider->shouldReceive('funds')->andReturn(collect([
        (object) ['externalId' => 'fund-1'],
    ]));
    $provider->shouldReceive('memberships')->andReturn(collect([
        (object) ['externalId' => 'membership-1'],
    ]));
    app()->instance(TicketingProvider::class, $provider);

    $stats = Livewire::test(JourneySyncHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[0]->getColor())->toBe('warning')
        ->and($stats[1]->getValue())->toBe('0')
        ->and($stats[1]->getColor())->toBe('success');
});

test('journey sync health widget flags mismatches between visible cms and provider sets', function () {
    DonationFund::factory()->create([
        'provider' => 'spektrix',
        'external_id' => 'fund-visible-only',
        'is_visible' => true,
    ]);
    Membership::factory()->create([
        'provider' => 'spektrix',
        'external_id' => 'membership-visible-only',
        'is_visible' => true,
    ]);

    $provider = mock(TicketingProvider::class);
    $provider->shouldReceive('providerKey')->andReturn('spektrix');
    $provider->shouldReceive('funds')->andReturn(collect([
        (object) ['externalId' => 'fund-provider-only'],
    ]));
    $provider->shouldReceive('memberships')->andReturn(collect([
        (object) ['externalId' => 'membership-provider-only'],
    ]));
    app()->instance(TicketingProvider::class, $provider);

    $stats = Livewire::test(JourneySyncHealthWidget::class)
        ->instance()
        ->getStats();

    expect($stats[1]->getValue())->toBe('4')
        ->and($stats[1]->getDescription())->toContain('Funds: 2')
        ->and($stats[1]->getDescription())->toContain('memberships: 2')
        ->and($stats[1]->getColor())->toBe('warning');
});
