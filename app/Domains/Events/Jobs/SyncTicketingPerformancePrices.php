<?php

declare(strict_types=1);

namespace App\Domains\Events\Jobs;

use App\Domains\Events\Actions\SyncPerformancePricesAction;
use App\Domains\Events\Enums\SyncRunStatus;
use App\Domains\Events\Models\Performance;
use App\Domains\Events\Models\SyncRun;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Throwable;

final class SyncTicketingPerformancePrices implements ShouldQueue
{
    use Batchable, Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    /**
     * @var list<int>
     */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public readonly int $syncRunId,
        public readonly int $performanceId,
    ) {}

    public function handle(SyncPerformancePricesAction $action): void
    {
        if ($this->batch()?->cancelled() === true) {
            return;
        }

        $action->execute(
            Performance::findOrFail($this->performanceId),
            SyncRun::findOrFail($this->syncRunId),
        );
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        return [
            'ticketing',
            'provider:spektrix',
            'sync:performance-prices',
            'sync-run:'.$this->syncRunId,
            'performance:'.$this->performanceId,
        ];
    }

    public function failed(?Throwable $exception): void
    {
        $syncRun = SyncRun::find($this->syncRunId);

        if ($syncRun === null) {
            return;
        }

        $syncRun->increment('performances_failed');
        $syncRun->update([
            'status' => SyncRunStatus::Failed,
            'error_message' => $exception === null
                ? 'Performance price sync job failed without an exception message.'
                : Str::limit($exception->getMessage(), 1000),
        ]);
    }
}
