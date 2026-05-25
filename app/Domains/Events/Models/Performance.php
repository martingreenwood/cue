<?php

declare(strict_types=1);

namespace App\Domains\Events\Models;

use Database\Factories\Domains\Events\Models\PerformanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'source_payload' => 'array',
            'synced_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): PerformanceFactory
    {
        return PerformanceFactory::new();
    }
}
