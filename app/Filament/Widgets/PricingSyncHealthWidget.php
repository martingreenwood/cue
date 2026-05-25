<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domains\Events\Enums\SyncRunStatus;
use App\Domains\Events\Models\Performance;
use App\Domains\Events\Models\SyncRun;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class PricingSyncHealthWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '30s';

    public function getStats(): array
    {
        $staleAfterMinutes = (int) config('ticketing.pricing.stale_after_minutes', 60);

        $relevantPerformances = Performance::query()
            ->where('is_on_sale', true)
            ->where('is_cancelled', false)
            ->where('starts_at', '>=', now());

        $totalRelevant = $relevantPerformances->count();

        $pricedCount = (clone $relevantPerformances)
            ->whereNotNull('prices_synced_at')
            ->whereNotNull('display_from_price_minor')
            ->count();

        $staleCount = (clone $relevantPerformances)
            ->where(function (Builder $query) use ($staleAfterMinutes): void {
                $query->whereNull('prices_synced_at')
                    ->orWhere('prices_synced_at', '<', now()->subMinutes($staleAfterMinutes));
            })
            ->count();

        $lastPriceSync = SyncRun::query()
            ->where('operation', 'performance-prices')
            ->where('status', SyncRunStatus::Succeeded->value)
            ->latest('finished_at')
            ->first();

        $recentPricingFailures = SyncRun::query()
            ->where('operation', 'performance-prices')
            ->where('status', SyncRunStatus::Failed->value)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return [
            Stat::make('Priced performances', "{$pricedCount} / {$totalRelevant}")
                ->description('Future on-sale with a display price')
                ->descriptionIcon('heroicon-m-currency-pound')
                ->color($pricedCount < $totalRelevant ? 'warning' : 'success'),

            Stat::make('Stale pricing', (string) $staleCount)
                ->description("Unpriced or older than {$staleAfterMinutes} min")
                ->descriptionIcon($staleCount > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($staleCount > 0 ? 'warning' : 'success'),

            Stat::make('Last price sync', $lastPriceSync
                ? $lastPriceSync->finished_at->diffForHumans()
                : 'Never synced'
            )
                ->description($recentPricingFailures > 0
                    ? "{$recentPricingFailures} failure(s) in last 7 days"
                    : 'No recent failures'
                )
                ->descriptionIcon($recentPricingFailures > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-arrow-path')
                ->color($recentPricingFailures > 0 ? 'danger' : ($lastPriceSync ? 'success' : 'warning')),
        ];
    }
}
