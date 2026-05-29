<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\RelationManagers;

use App\Domains\Events\Models\Performance;
use App\Filament\Resources\Performances\PerformanceResource;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PerformancesRelationManager extends RelationManager
{
    protected static string $relationship = 'performances';

    protected static ?string $relatedResource = PerformanceResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('starts_at')
                    ->label('Start time')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_on_sale')
                    ->label('On sale')
                    ->boolean(),
                IconColumn::make('is_cancelled')
                    ->label('Cancelled')
                    ->boolean(),
                TextColumn::make('display_from_price_minor')
                    ->label('Standard from')
                    ->money(currency: fn (Performance $record): string => $record->display_currency ?? 'GBP', divideBy: 100)
                    ->placeholder('Not synced'),
                IconColumn::make('has_dynamic_pricing')
                    ->label('Dynamic')
                    ->boolean(),
                TextColumn::make('prices_synced_at')
                    ->label('Price synced')
                    ->since()
                    ->placeholder('Never'),
                TextColumn::make('accessTerms.name')
                    ->label('Access')
                    ->badge()
                    ->placeholder('None'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('starts_at');
    }
}
