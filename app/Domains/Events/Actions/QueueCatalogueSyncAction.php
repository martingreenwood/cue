<?php

declare(strict_types=1);

namespace App\Domains\Events\Actions;

use App\Domains\Events\Enums\SyncRunStatus;
use App\Domains\Events\Jobs\SyncTicketingCatalogue;
use App\Domains\Events\Models\SyncRun;
use App\Domains\Ticketing\Contracts\TicketingProvider;

final class QueueCatalogueSyncAction
{
    public function __construct(private readonly TicketingProvider $provider) {}

    public function execute(): SyncRun
    {
        SyncRun::query()
            ->where('provider', $this->provider->providerKey())
            ->where('operation', 'catalogue')
            ->whereIn('status', [SyncRunStatus::Queued->value, SyncRunStatus::Running->value])
            ->where('queued_at', '<', now()->subMinutes((int) config('ticketing.catalogue.active_run_stale_after_minutes', 15)))
            ->update([
                'status' => SyncRunStatus::Failed,
                'finished_at' => now(),
                'error_message' => 'Catalogue sync was marked failed after exceeding the active run timeout.',
                'context' => ['reason' => 'active_run_timeout'],
            ]);

        $activeRun = SyncRun::query()
            ->where('provider', $this->provider->providerKey())
            ->where('operation', 'catalogue')
            ->whereIn('status', [SyncRunStatus::Queued->value, SyncRunStatus::Running->value])
            ->latest('id')
            ->first();

        if ($activeRun !== null) {
            return $activeRun;
        }

        $syncRun = SyncRun::create([
            'provider' => $this->provider->providerKey(),
            'operation' => 'catalogue',
            'status' => SyncRunStatus::Queued,
            'queued_at' => now(),
        ]);

        SyncTicketingCatalogue::dispatch($syncRun->getKey());

        return $syncRun;
    }
}
