<?php

declare(strict_types=1);

namespace App\Domains\Events\Actions;

use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\EventRedirect;

final class CreateSlugRedirectAction
{
    public function execute(Event $event, string $fromSlug, string $toSlug): ?EventRedirect
    {
        if ($fromSlug === $toSlug) {
            return null;
        }

        $prefix = rtrim((string) config('ticketing.event_path_prefix', '/events'), '/');
        $sourcePath = $prefix.'/'.$fromSlug;
        $destinationPath = $prefix.'/'.$toSlug;

        if ($sourcePath === $destinationPath) {
            return null;
        }

        return EventRedirect::updateOrCreate(
            ['source_path' => $sourcePath],
            [
                'event_id' => $event->getKey(),
                'destination_path' => $destinationPath,
                'status_code' => 301,
                'is_active' => true,
                'notes' => 'Auto-created on editorial slug change.',
            ],
        );
    }
}
