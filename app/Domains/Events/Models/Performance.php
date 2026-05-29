<?php

declare(strict_types=1);

namespace App\Domains\Events\Models;

use App\Domains\Events\Enums\FilterGroup;
use Carbon\CarbonImmutable;
use Database\Factories\Domains\Events\Models\PerformanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable|null $prices_synced_at
 * @property string $external_id
 * @property string|null $web_id
 * @property int|null $display_from_price_minor
 * @property string|null $display_currency
 * @property bool $is_on_sale
 * @property bool $has_dynamic_pricing
 */
class Performance extends Model
{
    /** @use HasFactory<PerformanceFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'provider',
        'external_id',
        'web_id',
        'external_plan_id',
        'external_price_list_id',
        'starts_at',
        'sales_start_at',
        'sales_end_at',
        'is_on_sale',
        'is_cancelled',
        'display_from_price_minor',
        'display_currency',
        'has_dynamic_pricing',
        'prices_synced_at',
        'source_payload',
        'synced_at',
    ];

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return HasMany<PerformancePrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(PerformancePrice::class);
    }

    /**
     * @return BelongsToMany<FilterTerm, $this>
     */
    public function accessTerms(): BelongsToMany
    {
        return $this->belongsToMany(FilterTerm::class, 'performance_access_term')
            ->where('filter_terms.filter_group', FilterGroup::Access->value);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'sales_start_at' => 'immutable_datetime',
            'sales_end_at' => 'immutable_datetime',
            'is_on_sale' => 'boolean',
            'is_cancelled' => 'boolean',
            'display_from_price_minor' => 'integer',
            'has_dynamic_pricing' => 'boolean',
            'prices_synced_at' => 'immutable_datetime',
            'source_payload' => 'array',
            'synced_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): PerformanceFactory
    {
        return PerformanceFactory::new();
    }
}
