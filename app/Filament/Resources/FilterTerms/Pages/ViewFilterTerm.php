<?php

declare(strict_types=1);

namespace App\Filament\Resources\FilterTerms\Pages;

use App\Filament\Resources\FilterTerms\FilterTermResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFilterTerm extends ViewRecord
{
    protected static string $resource = FilterTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
