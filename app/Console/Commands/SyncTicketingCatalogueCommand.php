<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Events\Actions\QueueCatalogueSyncAction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ticketing:sync-catalogue')]
#[Description('Queue a synchronisation of the public ticketing event catalogue.')]
class SyncTicketingCatalogueCommand extends Command
{
    public function handle(QueueCatalogueSyncAction $queueCatalogueSync): int
    {
        $syncRun = $queueCatalogueSync->execute();

        $message = $syncRun->wasRecentlyCreated
            ? "Catalogue sync queued as run #{$syncRun->getKey()}."
            : "Catalogue sync is already active as run #{$syncRun->getKey()}.";

        $this->components->info($message);

        return self::SUCCESS;
    }
}
