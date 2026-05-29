<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\Pages;

use App\Domains\Events\Actions\UpdateEventPublicationAction;
use App\Domains\Events\Models\Event;
use App\Filament\Resources\Events\EventResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewEvent extends ViewRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publishNow')
                ->label('Publish now')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Publish this event now?')
                ->modalDescription('This event will become visible on the public events pages immediately.')
                ->visible(fn (): bool => ! $this->isPubliclyVisible())
                ->action(function (): void {
                    $event = $this->eventRecord();

                    app(UpdateEventPublicationAction::class)->publishNow($event);
                    $event->unsetRelation('editorial')->load('editorial');

                    Notification::make()
                        ->title('Event published')
                        ->success()
                        ->send();
                }),
            Action::make('unpublish')
                ->label('Unpublish')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Unpublish this event?')
                ->modalDescription('This event will no longer appear on the public events pages.')
                ->visible(fn (): bool => $this->isPubliclyVisible())
                ->action(function (): void {
                    $event = $this->eventRecord();

                    app(UpdateEventPublicationAction::class)->unpublish($event);
                    $event->unsetRelation('editorial')->load('editorial');

                    Notification::make()
                        ->title('Event unpublished')
                        ->success()
                        ->send();
                }),
            EditAction::make(),
        ];
    }

    private function isPubliclyVisible(): bool
    {
        $editorial = $this->eventRecord()->editorial;

        return (bool) $editorial?->is_published
            && ($editorial->published_at === null || $editorial->published_at->isPast());
    }

    private function eventRecord(): Event
    {
        $event = $this->getRecord();

        assert($event instanceof Event);

        return $event;
    }
}
