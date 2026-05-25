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
