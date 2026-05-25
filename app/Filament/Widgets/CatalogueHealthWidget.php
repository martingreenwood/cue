<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domains\Events\Enums\SyncRunStatus;
use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\Performance;
use App\Domains\Events\Models\SyncRun;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CatalogueHealthWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    public function getStats(): array
    {
        $eventCount = Event::count();
        $performanceCount = Performance::count();

        $lastCatalogueSync = SyncRun::query()
            ->where('operation', 'catalogue')
            ->where('status', SyncRunStatus::Succeeded->value)
            ->latest('finished_at')
            ->first();

        $recentCatalogueFailures = SyncRun::query()
            ->where('operation', 'catalogue')
            ->where('status', SyncRunStatus::Failed->value)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return [
            Stat::make('Events', (string) $eventCount)
                ->description("{$performanceCount} performances")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Last catalogue sync', $lastCatalogueSync
                ? $lastCatalogueSync->finished_at->diffForHumans()
                : 'Never synced'
            )
                ->description($lastCatalogueSync
                    ? "{$lastCatalogueSync->events_synced} events, {$lastCatalogueSync->performances_synced} performances imported"
                    : 'Run a catalogue sync from Sync Runs'
                )
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($lastCatalogueSync ? 'success' : 'warning'),

            Stat::make('Catalogue failures', (string) $recentCatalogueFailures)
                ->description('Last 7 days')
                ->descriptionIcon($recentCatalogueFailures > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($recentCatalogueFailures > 0 ? 'danger' : 'success'),
        ];
    }
}
