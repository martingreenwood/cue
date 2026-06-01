<?php

declare(strict_types=1);

namespace App\Domains\Events\Models;

use Carbon\CarbonImmutable;
use Database\Factories\Domains\Events\Models\AvailabilitySnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable $captured_at
 */
class AvailabilitySnapshot extends Model
{
    /** @use HasFactory<AvailabilitySnapshotFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'provider',
        'sync_run_id',
        'future_on_sale_total',
        'future_on_sale_available',
        'future_on_sale_stale',
        'future_on_sale_unpriced',
        'captured_at',
    ];

    /**
     * @return BelongsTo<SyncRun, $this>
     */
    public function syncRun(): BelongsTo
    {
        return $this->belongsTo(SyncRun::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sync_run_id' => 'integer',
            'future_on_sale_total' => 'integer',
            'future_on_sale_available' => 'integer',
            'future_on_sale_stale' => 'integer',
            'future_on_sale_unpriced' => 'integer',
            'captured_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): AvailabilitySnapshotFactory
    {
        return AvailabilitySnapshotFactory::new();
    }
}
