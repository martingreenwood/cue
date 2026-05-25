<?php

declare(strict_types=1);

namespace App\Filament\Resources\Performances\RelationManagers;

use App\Domains\Events\Models\PerformancePrice;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('external_id')
            ->columns([
                TextColumn::make('ticket_type_name')
                    ->label('Ticket type')
                    ->searchable(),
                TextColumn::make('price_band_name')
                    ->label('Band')
                    ->searchable(),
                TextColumn::make('amount_minor')
                    ->label('Price')
                    ->money(currency: fn (PerformancePrice $record): string => $record->currency, divideBy: 100)
                    ->sortable(),
                IconColumn::make('is_band_default')
                    ->label('Default')
                    ->boolean(),
                IconColumn::make('is_dynamic_pricing_eligible')
                    ->label('Dynamic eligible')
                    ->boolean(),
                TextColumn::make('synced_at')
                    ->label('Synced')
                    ->since(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('amount_minor');
    }
}
