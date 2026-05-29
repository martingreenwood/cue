<?php

declare(strict_types=1);

namespace App\Filament\Resources\Performances\Tables;

use App\Domains\Events\Models\Performance;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PerformancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_on_sale')->label('On sale')->boolean(),
                IconColumn::make('is_cancelled')->label('Cancelled')->boolean(),
                TextColumn::make('display_from_price_minor')
                    ->label('Standard from')
                    ->money(currency: fn (Performance $record): string => $record->display_currency ?? 'GBP', divideBy: 100)
                    ->placeholder('Not synced'),
                IconColumn::make('has_dynamic_pricing')->label('Dynamic')->boolean(),
                TextColumn::make('accessTerms.name')
                    ->label('Access')
                    ->badge()
                    ->placeholder('None'),
                TextColumn::make('prices_synced_at')
                    ->label('Price synced')
                    ->since()
                    ->placeholder('Never'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('starts_at');
    }
}
