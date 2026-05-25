<?php

declare(strict_types=1);

namespace App\Domains\Events\Models;

use Database\Factories\Domains\Events\Models\EventRedirectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRedirect extends Model
{
    /** @use HasFactory<EventRedirectFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'source_path',
        'destination_path',
        'status_code',
        'is_active',
        'notes',
    ];

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): EventRedirectFactory
    {
        return EventRedirectFactory::new();
    }
}
