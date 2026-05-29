<?php

declare(strict_types=1);

namespace App\Domains\Events\Actions;

use App\Domains\Events\Models\Event;
use App\Domains\Events\Models\EventEditorial;

final class UpdateEventPublicationAction
{
    public function publishNow(Event $event): EventEditorial
    {
        return EventEditorial::updateOrCreate(
            ['event_id' => $event->getKey()],
            [
                'is_published' => true,
                'published_at' => now(),
            ],
        );
    }

    public function unpublish(Event $event): EventEditorial
    {
        return EventEditorial::updateOrCreate(
            ['event_id' => $event->getKey()],
            ['is_published' => false],
        );
    }
}
