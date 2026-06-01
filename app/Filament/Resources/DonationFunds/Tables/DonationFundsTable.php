<?php

declare(strict_types=1);

namespace App\Filament\Resources\DonationFunds\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DonationFundsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Code')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('default_donation_amount_minor')
                    ->label('Default')
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '—' : '£'.number_format($state / 100, 2))
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('synced_at')
                    ->since()
                    ->label('Synced')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
