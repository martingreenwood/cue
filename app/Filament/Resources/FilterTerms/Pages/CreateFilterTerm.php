<?php

declare(strict_types=1);

namespace App\Filament\Resources\FilterTerms\Pages;

use App\Filament\Resources\FilterTerms\FilterTermResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFilterTerm extends CreateRecord
{
    protected static string $resource = FilterTermResource::class;
}
