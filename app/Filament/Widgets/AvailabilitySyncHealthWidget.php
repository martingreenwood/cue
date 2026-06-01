<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domains\Events\Models\AvailabilitySnapshot;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AvailabilitySyncHealthWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected ?string $pollingInterval = '30s';

    public function getStats(): array
    {
        $snapshot = AvailabilitySnapshot::query()->latest('captured_at')->first();

        if ($snapshot === null) {
            return [
                Stat::make('Availability snapshots', 'No snapshot yet')
                    ->description('Run catalogue or price sync to capture live availability health.')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color('warning'),
            ];
        }

        $total = $snapshot->future_on_sale_total;
        $available = $snapshot->future_on_sale_available;
        $stale = $snapshot->future_on_sale_stale;
        $unpriced = $snapshot->future_on_sale_unpriced;

        $ageColor = $snapshot->captured_at->lt(now()->subMinutes(30)) ? 'warning' : 'success';
        $qualityColor = ($stale > 0 || $unpriced > 0) ? 'warning' : 'success';

        return [
            Stat::make('Available future performances', "{$available} / {$total}")
                ->description('Future on-sale currently within sales window')
                ->descriptionIcon('heroicon-m-ticket')
                ->color($qualityColor),

            Stat::make('Snapshot quality', "{$stale} stale, {$unpriced} unpriced")
                ->description('Stale based on pricing sync freshness threshold')
                ->descriptionIcon($stale > 0 || $unpriced > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($qualityColor),

            Stat::make('Last availability snapshot', $snapshot->captured_at->diffForHumans())
                ->description('Captured from latest catalogue or pricing sync')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($ageColor),
        ];
    }
}
