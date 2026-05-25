<?php

declare(strict_types=1);

namespace App\Domains\Events\Models;

use Database\Factories\Domains\Events\Models\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'provider',
        'external_id',
        'slug',
        'title',
        'summary',
        'description_html',
        'duration_minutes',
        'image_url',
        'thumbnail_url',
        'local_image_path',
        'image_alt',
        'is_on_sale',
        'first_performance_at',
        'last_performance_at',
        'source_payload',
        'synced_at',
    ];

    /**
     * @return HasMany<Performance, $this>
     */
    public function performances(): HasMany
    {
        return $this->hasMany(Performance::class);
    }

    /**
     * @return HasOne<EventEditorial, $this>
     */
    public function editorial(): HasOne
    {
        return $this->hasOne(EventEditorial::class);
    }

    /**
     * @return HasMany<EventRedirect, $this>
     */
    public function redirects(): HasMany
    {
        return $this->hasMany(EventRedirect::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'is_on_sale' => 'boolean',
            'first_performance_at' => 'immutable_datetime',
            'last_performance_at' => 'immutable_datetime',
            'source_payload' => 'array',
            'synced_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): EventFactory
    {
        return EventFactory::new();
    }
}
