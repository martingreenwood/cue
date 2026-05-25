<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('local_image_path')
                    ->label('')
                    ->disk('public')
                    ->height(40)
                    ->width(60)
                    ->defaultImageUrl(null),
                TextColumn::make('title')
                    ->label('Provider title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('editorial.title')
                    ->label('Editorial title')
                    ->placeholder('Uses source')
                    ->searchable(),
                IconColumn::make('editorial.is_published')
                    ->label('Published')
                    ->boolean(),
                IconColumn::make('is_on_sale')
                    ->label('On sale')
                    ->boolean(),
                TextColumn::make('performances_count')
                    ->label('Performances')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_performance_at')
                    ->label('Last performance')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('synced_at')
                    ->label('Synced')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('last_performance_at');
    }
}
