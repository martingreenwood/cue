<?php

declare(strict_types=1);

namespace App\Filament\Resources\SyncRuns\Pages;

use App\Domains\Events\Actions\QueueCatalogueSyncAction;
use App\Domains\Events\Actions\QueuePerformancePriceSyncAction;
use App\Filament\Resources\SyncRuns\SyncRunResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSyncRuns extends ListRecords
{
    protected static string $resource = SyncRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncCatalogue')
                ->label('Sync catalogue')
                ->action(function (QueueCatalogueSyncAction $queueCatalogueSync): void {
                    $syncRun = $queueCatalogueSync->execute();

                    Notification::make()
                        ->title("Catalogue sync queued as run #{$syncRun->getKey()}.")
                        ->success()
                        ->send();
                }),
            Action::make('syncPrices')
                ->label('Sync prices')
                ->action(function (QueuePerformancePriceSyncAction $queuePerformancePriceSync): void {
                    $syncRun = $queuePerformancePriceSync->execute();

                    Notification::make()
                        ->title("Price sync active as run #{$syncRun->getKey()}.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
