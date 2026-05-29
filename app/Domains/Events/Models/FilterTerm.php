<?php

declare(strict_types=1);

namespace App\Domains\Events\Models;

use App\Domains\Events\Enums\FilterGroup;
use Database\Factories\Domains\Events\Models\FilterTermFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property FilterGroup $filter_group
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $sort_order
 * @property int|null $access_performances_count
 * @property int|null $offer_events_count
 * @property int|null $what_events_count
 */
class FilterTerm extends Model
{
    /** @use HasFactory<FilterTermFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'filter_group',
        'name',
        'slug',
        'description',
        'sort_order',
    ];

    /**
     * @return BelongsToMany<Event, $this>
     */
    public function whatEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_what_term');
    }

    /**
     * @return BelongsToMany<Event, $this>
     */
    public function offerEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_offer_term');
    }

    /**
     * @return BelongsToMany<Performance, $this>
     */
    public function accessPerformances(): BelongsToMany
    {
        return $this->belongsToMany(Performance::class, 'performance_access_term');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'filter_group' => FilterGroup::class,
            'sort_order' => 'integer',
        ];
    }

    protected static function newFactory(): FilterTermFactory
    {
        return FilterTermFactory::new();
    }
}
