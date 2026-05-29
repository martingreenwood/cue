<?php

declare(strict_types=1);

namespace App\Filament\Resources\FilterTerms\Pages;

use App\Filament\Resources\FilterTerms\FilterTermResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFilterTerms extends ListRecords
{
    protected static string $resource = FilterTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
