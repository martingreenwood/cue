<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\Pages;

use App\Domains\Events\Actions\CreateSlugRedirectAction;
use App\Domains\Events\Models\EventRedirect;
use App\Filament\Resources\Events\EventResource;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    private ?string $slugBeforeSave = null;

    protected function beforeSave(): void
    {
        $this->slugBeforeSave = $this->record->editorial?->slug;
    }

    protected function afterSave(): void
    {
        $this->record->load('editorial');

        $editorial = $this->record->editorial;
        $newSlug = $editorial?->slug;
        $oldSlug = $this->slugBeforeSave;
        $providerSlug = $this->record->slug;

        $redirect = match (true) {
            // Slug changed from one value to another.
            $newSlug !== null && $oldSlug !== null && $newSlug !== $oldSlug => app(CreateSlugRedirectAction::class)->execute($this->record, $oldSlug, $newSlug),

            // Slug set for the first time and differs from the provider slug.
            $newSlug !== null && $oldSlug === null && $newSlug !== $providerSlug => app(CreateSlugRedirectAction::class)->execute($this->record, $providerSlug, $newSlug),

            // Slug cleared — redirect the old editorial path back to the provider slug.
            $newSlug === null && $oldSlug !== null && $oldSlug !== $providerSlug => app(CreateSlugRedirectAction::class)->execute($this->record, $oldSlug, $providerSlug),

            default => null,
        };

        if ($redirect instanceof EventRedirect) {
            Notification::make()
                ->title('Redirect created')
                ->body("{$redirect->source_path} → {$redirect->destination_path}")
                ->info()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
