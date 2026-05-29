<?php

declare(strict_types=1);

namespace App\Filament\Resources\FilterTerms\Schemas;

use App\Domains\Events\Enums\FilterGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FilterTermForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Public Filter Term')
                    ->description('Create a label that editors may assign to events or individual performances on the public programme.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('filter_group')
                                ->label('Filter group')
                                ->options(FilterGroup::options())
                                ->helperText('What and Offers are assigned to events. Access is assigned to performances.')
                                ->required()
                                ->disabledOn('edit'),
                            TextInput::make('sort_order')
                                ->label('Display order')
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->minValue(0),
                            TextInput::make('name')
                                ->label('Public label')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('slug')
                                ->label('Public identifier')
                                ->helperText('Stable identifier for future filter URLs, for example "audio-described".')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            Textarea::make('description')
                                ->label('Internal guidance')
                                ->helperText('Optional explanation to help editors apply this term consistently.')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
