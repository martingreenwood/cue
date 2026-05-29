<?php

declare(strict_types=1);

namespace App\Filament\Resources\Performances\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PerformanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Synced Ticketing Source')
                    ->description('Provider-managed performance data is shown for reference and overwritten by catalogue sync.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('event.title')
                                ->label('Event')
                                ->disabled(),
                            TextInput::make('external_id')
                                ->label('Provider performance ID')
                                ->disabled(),
                            DateTimePicker::make('starts_at')
                                ->label('Starts')
                                ->disabled(),
                            Toggle::make('is_on_sale')
                                ->label('Provider on sale')
                                ->disabled(),
                        ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Public Access')
                    ->description('Cue-owned access metadata for this specific performance. It survives provider resynchronisation.')
                    ->schema([
                        Select::make('accessTerms')
                            ->label('Access provisions')
                            ->helperText('Assign only when this performance provides the access provision. Public Access filters include upcoming, non-cancelled tagged performances only.')
                            ->relationship(titleAttribute: 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
