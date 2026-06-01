<?php

declare(strict_types=1);

use App\Domains\Events\Actions\SyncCatalogueAction;
use App\Domains\Events\Enums\SyncRunStatus;
use App\Domains\Events\Models\AvailabilitySnapshot;
use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\Performance;
use App\Domains\Events\Models\SyncRun;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    // Prevent image download jobs from executing synchronously during sync tests.
    Queue::fake();
});

test('it stores and updates a local catalogue idempotently', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*/events*' => Http::sequence()
            ->push([spektrixEventPayload()])
            ->push([spektrixEventPayload(['name' => 'Updated Production'])]),
        '*/instances*' => Http::sequence()
            ->push([spektrixPerformancePayload()])
            ->push([spektrixPerformancePayload(['isOnSale' => false])]),
    ]);

    $completed = app(SyncCatalogueAction::class)->execute(SyncRun::factory()->create());

    expect($completed->status)->toBe(SyncRunStatus::Succeeded)
        ->and($completed->events_synced)->toBe(1)
        ->and($completed->performances_synced)->toBe(1)
        ->and(Event::query()->count())->toBe(1)
        ->and(Performance::query()->count())->toBe(1)
        ->and(AvailabilitySnapshot::query()->count())->toBe(1);

    $event = Event::query()->sole();
    $originalSlug = $event->slug;

    expect($event->title)->toBe('Aldwych Theatre -> Integration Controls 01')
        ->and($event->performances()->sole()->external_plan_id)->toBe('201AGBHDRLQHNHPHKKMPKLGPMDRDTDMVL');

    app(SyncCatalogueAction::class)->execute(SyncRun::factory()->create());

    expect(Event::query()->count())->toBe(1)
        ->and(Performance::query()->count())->toBe(1)
        ->and(Event::query()->sole()->title)->toBe('Updated Production')
        ->and(Event::query()->sole()->slug)->toBe($originalSlug)
        ->and(Performance::query()->sole()->is_on_sale)->toBeFalse();
});

test('it records provider failures on the sync run', function () {
    Http::preventStrayRequests();
    Http::fake(['*/events*' => Http::response([], 503)]);

    $run = SyncRun::factory()->create();

    expect(fn () => app(SyncCatalogueAction::class)->execute($run))
        ->toThrow(RequestException::class);

    expect($run->refresh()->status)->toBe(SyncRunStatus::Failed)
        ->and($run->finished_at)->not->toBeNull()
        ->and($run->error_message)->not->toBeNull();
});
