<?php

declare(strict_types=1);

use App\Domains\Events\Actions\SyncCatalogueAction;
use App\Domains\Events\Enums\SyncRunStatus;
use App\Domains\Events\Jobs\SyncTicketingCatalogue;
use App\Domains\Events\Models\SyncRun;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

test('the command records and queues a catalogue synchronisation run', function () {
    Queue::fake();

    $this->artisan('ticketing:sync-catalogue')
        ->expectsOutputToContain('Catalogue sync queued as run #')
        ->assertSuccessful();

    $run = SyncRun::query()->sole();

    expect($run->status)->toBe(SyncRunStatus::Queued);

    Queue::assertPushed(SyncTicketingCatalogue::class, function (SyncTicketingCatalogue $job) use ($run): bool {
        return $job->syncRunId === $run->getKey()
            && $job->timeout === 50
            && in_array('sync:catalogue', $job->tags(), true);
    });
});

test('the command does not dispatch overlapping catalogue synchronisation runs', function () {
    Queue::fake();

    $activeRun = SyncRun::factory()->create([
        'operation' => 'catalogue',
        'status' => SyncRunStatus::Running,
    ]);

    $this->artisan('ticketing:sync-catalogue')
        ->expectsOutputToContain("Catalogue sync is already active as run #{$activeRun->getKey()}.")
        ->assertSuccessful();

    expect(SyncRun::query()->count())->toBe(1);

    Queue::assertNothingPushed();
});

test('the command replaces an abandoned catalogue synchronisation run', function () {
    Queue::fake();
    config(['ticketing.catalogue.active_run_stale_after_minutes' => 15]);

    $abandonedRun = SyncRun::factory()->create([
        'operation' => 'catalogue',
        'status' => SyncRunStatus::Running,
        'queued_at' => now()->subMinutes(16),
    ]);

    $this->artisan('ticketing:sync-catalogue')
        ->expectsOutputToContain('Catalogue sync queued as run #')
        ->assertSuccessful();

    expect($abandonedRun->refresh()->status)->toBe(SyncRunStatus::Failed)
        ->and($abandonedRun->error_message)->toContain('active run timeout')
        ->and($abandonedRun->context)->toBe(['reason' => 'active_run_timeout'])
        ->and(SyncRun::query()->count())->toBe(2);

    Queue::assertPushed(SyncTicketingCatalogue::class);
});

test('an abandoned catalogue job does not run if it is delivered after replacement', function () {
    Http::preventStrayRequests();

    $abandonedRun = SyncRun::factory()->create([
        'operation' => 'catalogue',
        'status' => SyncRunStatus::Failed,
        'context' => ['reason' => 'active_run_timeout'],
    ]);

    (new SyncTicketingCatalogue($abandonedRun->getKey()))->handle(app(SyncCatalogueAction::class));

    expect($abandonedRun->refresh()->status)->toBe(SyncRunStatus::Failed);
});
