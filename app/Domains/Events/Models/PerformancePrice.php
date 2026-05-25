<?php

declare(strict_types=1);

namespace App\Domains\Events\Models;

use Database\Factories\Domains\Events\Models\PerformancePriceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformancePrice extends Model
{
    /** @use HasFactory<PerformancePriceFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'performance_id',
        'provider',
        'external_id',
        'ticket_type_external_id',
        'ticket_type_name',
        'price_band_external_id',
        'price_band_name',
        'amount_minor',
        'currency',
        'is_band_default',
        'is_dynamic_pricing_eligible',
        'source_payload',
        'synced_at',
    ];

    /**
     * @return BelongsTo<Performance, $this>
     */
    public function performance(): BelongsTo
    {
        return $this->belongsTo(Performance::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'is_band_default' => 'boolean',
            'is_dynamic_pricing_eligible' => 'boolean',
            'source_payload' => 'array',
            'synced_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): PerformancePriceFactory
    {
        return PerformancePriceFactory::new();
    }
}
