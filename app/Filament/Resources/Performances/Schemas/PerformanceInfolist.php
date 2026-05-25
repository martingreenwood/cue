<?php

declare(strict_types=1);

namespace App\Filament\Resources\Performances\Schemas;

use App\Domains\Events\Models\Performance;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PerformanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Performance')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('event.title')->label('Event'),
                            TextEntry::make('external_id')->label('Provider performance ID'),
                            TextEntry::make('starts_at')->label('Starts')->dateTime(),
                            IconEntry::make('is_on_sale')->label('On sale')->boolean(),
                            IconEntry::make('is_cancelled')->label('Cancelled')->boolean(),
                            TextEntry::make('external_price_list_id')->label('Provider price list ID'),
                            TextEntry::make('synced_at')->label('Catalogue synced')->dateTime(),
                        ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Pricing Summary')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('display_from_price_minor')
                                ->label('Standard from price')
                                ->money(currency: fn (Performance $record): string => $record->display_currency ?? 'GBP', divideBy: 100)
                                ->placeholder('Not yet synced'),
                            IconEntry::make('has_dynamic_pricing')
                                ->label('Dynamic pricing present')
                                ->boolean(),
                            TextEntry::make('prices_synced_at')
                                ->label('Prices synced')
                                ->dateTime()
                                ->placeholder('Never'),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
