<?php

declare(strict_types=1);

namespace App\Domains\Events\Actions;

use App\Domains\Events\Models\AvailabilitySnapshot;
use App\Domains\Events\Models\Performance;
use App\Domains\Events\Models\SyncRun;
use App\Domains\Ticketing\Contracts\TicketingProvider;
use Illuminate\Database\Eloquent\Builder;

final class CaptureAvailabilitySnapshotAction
{
    public function __construct(private readonly TicketingProvider $provider) {}

    public function execute(?int $syncRunId = null): AvailabilitySnapshot
    {
        $staleAfterMinutes = (int) config('ticketing.pricing.stale_after_minutes', 60);
        $now = now();

        $relevantPerformances = Performance::query()
            ->where('provider', $this->provider->providerKey())
            ->where('is_on_sale', true)
            ->where('is_cancelled', false)
            ->where('starts_at', '>=', $now);

        $total = $relevantPerformances->count();

        $available = (clone $relevantPerformances)
            ->where(function (Builder $query) use ($now): void {
                $query->whereNull('sales_start_at')
                    ->orWhere('sales_start_at', '<=', $now);
            })
            ->where(function (Builder $query) use ($now): void {
                $query->whereNull('sales_end_at')
                    ->orWhere('sales_end_at', '>=', $now);
            })
            ->count();

        $stale = (clone $relevantPerformances)
            ->where(function (Builder $query) use ($staleAfterMinutes, $now): void {
                $query->whereNull('prices_synced_at')
                    ->orWhere('prices_synced_at', '<', $now->subMinutes($staleAfterMinutes));
            })
            ->count();

        $unpriced = (clone $relevantPerformances)
            ->whereNull('display_from_price_minor')
            ->count();

        return AvailabilitySnapshot::create([
            'provider' => $this->provider->providerKey(),
            'sync_run_id' => $syncRunId !== null && SyncRun::query()->whereKey($syncRunId)->exists() ? $syncRunId : null,
            'future_on_sale_total' => $total,
            'future_on_sale_available' => $available,
            'future_on_sale_stale' => $stale,
            'future_on_sale_unpriced' => $unpriced,
            'captured_at' => $now,
        ]);
    }
}
