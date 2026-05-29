<?php

declare(strict_types=1);

namespace App\Domains\Events\Models;

use App\Domains\Events\Enums\FilterGroup;
use Carbon\CarbonImmutable;
use Database\Factories\Domains\Events\Models\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property CarbonImmutable|null $first_performance_at
 * @property CarbonImmutable|null $last_performance_at
 */
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
     * @return BelongsToMany<FilterTerm, $this>
     */
    public function whatTerms(): BelongsToMany
    {
        return $this->belongsToMany(FilterTerm::class, 'event_what_term')
            ->where('filter_terms.filter_group', FilterGroup::What->value);
    }

    /**
     * @return BelongsToMany<FilterTerm, $this>
     */
    public function offerTerms(): BelongsToMany
    {
        return $this->belongsToMany(FilterTerm::class, 'event_offer_term')
            ->where('filter_terms.filter_group', FilterGroup::Offers->value);
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
