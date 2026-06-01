<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\Tables;

use App\Domains\Events\Actions\UpdateEventPublicationAction;
use App\Domains\Events\Models\Event;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Event')
                    ->state(fn (Event $record): string => $record->editorial?->title ?: $record->title)
                    ->description(fn (Event $record): ?string => $record->editorial?->title ? 'Source: '.$record->title : null)
                    ->limit(60)
                    ->searchable()
                    ->sortable(),
                IconColumn::make('editorial.is_published')
                    ->label('Live')
                    ->boolean(),
                IconColumn::make('is_on_sale')
                    ->label('On sale')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('performances_count')
                    ->label('Dates')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_performance_at')
                    ->label('Until')
                    ->date('j M Y')
                    ->sortable(),
                TextColumn::make('synced_at')
                    ->label('Synced')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('publishNow')
                    ->label('Publish')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Event $record): bool => ! self::isPubliclyVisible($record))
                    ->action(function (Event $record): void {
                        app(UpdateEventPublicationAction::class)->publishNow($record);

                        Notification::make()
                            ->title('Event published')
                            ->success()
                            ->send();
                    }),
                Action::make('unpublish')
                    ->label('Unpublish')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Event $record): bool => self::isPubliclyVisible($record))
                    ->action(function (Event $record): void {
                        app(UpdateEventPublicationAction::class)->unpublish($record);

                        Notification::make()
                            ->title('Event unpublished')
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('last_performance_at');
    }

    private static function isPubliclyVisible(Event $event): bool
    {
        $editorial = $event->editorial;

        return (bool) $editorial?->is_published
            && ($editorial->published_at === null || $editorial->published_at->isPast());
    }
}
