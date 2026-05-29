<?php

declare(strict_types=1);

namespace App\Filament\Resources\FilterTerms\Schemas;

use App\Domains\Events\Enums\FilterGroup;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FilterTermInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Public Filter Term')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('filter_group')
                                ->label('Filter group')
                                ->formatStateUsing(fn (FilterGroup $state): string => $state->label())
                                ->badge(),
                            TextEntry::make('sort_order')
                                ->label('Display order')
                                ->numeric(),
                            TextEntry::make('name')
                                ->label('Public label'),
                            TextEntry::make('slug')
                                ->label('Public identifier'),
                            TextEntry::make('description')
                                ->label('Internal guidance')
                                ->placeholder('Not set')
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
