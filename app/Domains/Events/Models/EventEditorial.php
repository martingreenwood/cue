<?php

declare(strict_types=1);

namespace App\Domains\Events\Models;

use Database\Factories\Domains\Events\Models\EventEditorialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventEditorial extends Model
{
    /** @use HasFactory<EventEditorialFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'title',
        'slug',
        'summary',
        'description_html',
        'hero_image_path',
        'hero_image_alt',
        'seo_title',
        'seo_description',
        'is_published',
        'published_at',
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
            'is_published' => 'boolean',
            'published_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): EventEditorialFactory
    {
        return EventEditorialFactory::new();
    }
}
