<?php

declare(strict_types=1);

namespace App\Filament\Resources\FilterTerms\Pages;

use App\Filament\Resources\FilterTerms\FilterTermResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFilterTerm extends EditRecord
{
    protected static string $resource = FilterTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
