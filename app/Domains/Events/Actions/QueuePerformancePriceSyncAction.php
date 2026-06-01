<?php

declare(strict_types=1);

namespace App\Domains\Events\Actions;

use App\Domains\Events\Enums\SyncRunStatus;
use App\Domains\Events\Jobs\SyncTicketingPerformancePrices;
use App\Domains\Events\Models\Performance;
use App\Domains\Events\Models\SyncRun;
use App\Domains\Ticketing\Contracts\TicketingProvider;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Throwable;

final class QueuePerformancePriceSyncAction
{
    public function __construct(
        private readonly TicketingProvider $provider,
        private readonly CaptureAvailabilitySnapshotAction $captureAvailabilitySnapshot,
    ) {}

    public function execute(?int $performanceId = null): SyncRun
    {
        $activeRun = SyncRun::query()
            ->where('provider', $this->provider->providerKey())
            ->where('operation', 'performance-prices')
            ->whereIn('status', [SyncRunStatus::Queued->value, SyncRunStatus::Running->value])
            ->latest('id')
            ->first();

        if ($activeRun !== null) {
            return $activeRun;
        }

        $performanceIds = Performance::query()
            ->where('is_on_sale', true)
            ->where('is_cancelled', false)
            ->where('starts_at', '>=', now())
            ->when($performanceId !== null, fn ($query) => $query->whereKey($performanceId))
            ->orderBy('id')
            ->pluck('id');

        $syncRun = SyncRun::create([
            'provider' => $this->provider->providerKey(),
            'operation' => 'performance-prices',
            'status' => SyncRunStatus::Queued,
            'queued_at' => now(),
            'performances_queued' => $performanceIds->count(),
        ]);

        if ($performanceIds->isEmpty()) {
            $syncRun->update([
                'status' => SyncRunStatus::Succeeded,
                'started_at' => now(),
                'finished_at' => now(),
            ]);

            $this->captureAvailabilitySnapshot->execute((int) $syncRun->getKey());

            return $syncRun->refresh();
        }

        $syncRun->update([
            'status' => SyncRunStatus::Running,
            'started_at' => now(),
        ]);

        $syncRunId = $syncRun->getKey();
        $jobs = $performanceIds
            ->map(fn (int $performanceId): SyncTicketingPerformancePrices => new SyncTicketingPerformancePrices($syncRunId, $performanceId))
            ->all();

        try {
            Bus::batch($jobs)
                ->name("Ticketing performance prices run #{$syncRunId}")
                ->allowFailures()
                ->then(function (Batch $batch) use ($syncRunId): void {
                    SyncRun::query()
                        ->whereKey($syncRunId)
                        ->where('status', '!=', SyncRunStatus::Failed->value)
                        ->update(['status' => SyncRunStatus::Succeeded]);
                })
                ->catch(function (Batch $batch, Throwable $exception) use ($syncRunId): void {
                    SyncRun::query()->whereKey($syncRunId)->update([
                        'status' => SyncRunStatus::Failed,
                        'error_message' => Str::limit($exception->getMessage(), 1000),
                    ]);
                })
                ->finally(function (Batch $batch) use ($syncRunId): void {
                    SyncRun::query()->whereKey($syncRunId)->update(['finished_at' => now()]);
                    $this->captureAvailabilitySnapshot->execute($syncRunId);
                })
                ->dispatch();
        } catch (Throwable $exception) {
            $syncRun->update([
                'status' => SyncRunStatus::Failed,
                'finished_at' => now(),
                'error_message' => Str::limit($exception->getMessage(), 1000),
            ]);

            throw $exception;
        }

        return $syncRun->refresh();
    }
}
