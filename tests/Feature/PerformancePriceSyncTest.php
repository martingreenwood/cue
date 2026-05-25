<?php

declare(strict_types=1);

use App\Domains\Events\Actions\SyncPerformancePricesAction;
use App\Domains\Events\Enums\SyncRunStatus;
use App\Domains\Events\Jobs\SyncTicketingPerformancePrices;
use App\Domains\Events\Models\Performance;
use App\Domains\Events\Models\PerformancePrice;
use App\Domains\Events\Models\SyncRun;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

test('it persists prices and uses only standard prices for the headline from amount', function () {
    Http::preventStrayRequests();
    Http::fake(['*/instances/*/price-list' => Http::response(spektrixPriceListPayload())]);

    $performance = Performance::factory()->create();
    $run = SyncRun::factory()->create(['operation' => 'performance-prices']);

    app(SyncPerformancePricesAction::class)->execute($performance, $run);

    $performance->refresh();

    expect(PerformancePrice::query()->count())->toBe(3)
        ->and($performance->display_from_price_minor)->toBe(2000)
        ->and($performance->display_currency)->toBe('GBP')
        ->and($performance->has_dynamic_pricing)->toBeTrue()
        ->and($performance->prices_synced_at)->not->toBeNull()
        ->and($run->refresh()->performances_synced)->toBe(1)
        ->and($run->prices_synced)->toBe(3);
});

test('it replaces stale price rows during an idempotent refresh', function () {
    $performance = Performance::factory()->create();
    $run = SyncRun::factory()->create(['operation' => 'performance-prices']);

    $replacementPayload = spektrixPriceListPayload();
    $replacementPayload['prices'] = [
        [
            'id' => 'default-band-a',
            'isBandDefault' => true,
            'amount' => 42.50,
            'ticketType' => [
                'id' => 'full-price',
                'name' => 'Full Price',
                'attribute_EligibleForDynamicPricing' => true,
            ],
            'priceBand' => ['id' => 'band-a', 'name' => 'Band A'],
        ],
    ];

    Http::preventStrayRequests();
    Http::fake([
        '*/instances/*/price-list' => Http::sequence()
            ->push(spektrixPriceListPayload())
            ->push($replacementPayload),
    ]);

    app(SyncPerformancePricesAction::class)->execute($performance, $run);
    app(SyncPerformancePricesAction::class)->execute($performance, $run);

    expect(PerformancePrice::query()->count())->toBe(1)
        ->and(PerformancePrice::query()->sole()->amount_minor)->toBe(4250)
        ->and($performance->refresh()->display_from_price_minor)->toBe(4250);
});

test('the command batches only current on-sale performance price refreshes', function () {
    Bus::fake();

    $eligible = Performance::factory()->create(['starts_at' => now()->addDay()]);
    Performance::factory()->create(['starts_at' => now()->subDay()]);
    Performance::factory()->create(['is_on_sale' => false]);
    Performance::factory()->create(['is_cancelled' => true]);

    $this->artisan('ticketing:sync-prices')
        ->expectsOutputToContain('Performance price sync queued as run #')
        ->assertSuccessful();

    $run = SyncRun::query()->sole();

    expect($run->operation)->toBe('performance-prices')
        ->and($run->status)->toBe(SyncRunStatus::Running)
        ->and($run->performances_queued)->toBe(1);

    Bus::assertBatched(function (PendingBatch $batch) use ($eligible): bool {
        $job = $batch->jobs->first();

        return $batch->jobs->count() === 1
            && $job instanceof SyncTicketingPerformancePrices
            && $job->syncRunId > 0
            && $job->performanceId === $eligible->getKey()
            && in_array('sync:performance-prices', $job->tags(), true);
    });
});

test('the command may target one local performance for a controlled price refresh', function () {
    Bus::fake();

    $selected = Performance::factory()->create();
    Performance::factory()->create();

    $this->artisan("ticketing:sync-prices --performance={$selected->getKey()}")
        ->assertSuccessful();

    expect(SyncRun::query()->sole()->performances_queued)->toBe(1);

    Bus::assertBatched(function (PendingBatch $batch) use ($selected): bool {
        $job = $batch->jobs->sole();

        return $job instanceof SyncTicketingPerformancePrices
            && $job->performanceId === $selected->getKey();
    });
});

test('the command does not dispatch overlapping performance price refresh runs', function () {
    Bus::fake();

    $activeRun = SyncRun::factory()->create([
        'operation' => 'performance-prices',
        'status' => SyncRunStatus::Running,
    ]);
    Performance::factory()->create();

    $this->artisan('ticketing:sync-prices')
        ->expectsOutputToContain("Performance price sync is already active as run #{$activeRun->getKey()}.")
        ->assertSuccessful();

    expect(SyncRun::query()->count())->toBe(1);

    Bus::assertNothingBatched();
});
