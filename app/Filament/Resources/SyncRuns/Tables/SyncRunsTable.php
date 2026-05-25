<?php

declare(strict_types=1);

namespace App\Filament\Resources\SyncRuns\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SyncRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Run')
                    ->sortable(),
                TextColumn::make('operation')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('events_synced')
                    ->label('Events')
                    ->numeric(),
                TextColumn::make('performances_synced')
                    ->label('Performances')
                    ->numeric(),
                TextColumn::make('prices_synced')
                    ->label('Prices')
                    ->numeric(),
                TextColumn::make('performances_failed')
                    ->label('Failed')
                    ->numeric(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
