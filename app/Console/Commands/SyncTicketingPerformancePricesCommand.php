<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Events\Actions\QueuePerformancePriceSyncAction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ticketing:sync-prices {--performance= : Refresh one local performance ID only}')]
#[Description('Queue a refresh of current public ticketing performance prices.')]
class SyncTicketingPerformancePricesCommand extends Command
{
    public function handle(QueuePerformancePriceSyncAction $queuePerformancePriceSync): int
    {
        $performanceOption = $this->option('performance');

        if ($performanceOption !== null && (! ctype_digit($performanceOption) || (int) $performanceOption < 1)) {
            $this->components->error('The performance option must be a positive local performance ID.');

            return self::FAILURE;
        }

        $syncRun = $queuePerformancePriceSync->execute(
            $performanceOption === null ? null : (int) $performanceOption,
        );

        $message = $syncRun->wasRecentlyCreated
            ? "Performance price sync queued as run #{$syncRun->getKey()}."
            : "Performance price sync is already active as run #{$syncRun->getKey()}.";

        $this->components->info($message);

        return self::SUCCESS;
    }
}
