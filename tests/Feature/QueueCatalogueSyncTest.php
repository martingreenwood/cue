<?php

declare(strict_types=1);

use App\Domains\Events\Enums\SyncRunStatus;
use App\Domains\Events\Jobs\SyncTicketingCatalogue;
use App\Domains\Events\Models\SyncRun;
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
