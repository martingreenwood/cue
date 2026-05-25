<?php

declare(strict_types=1);

namespace App\Domains\Events\Models;

use App\Domains\Events\Enums\SyncRunStatus;
use Database\Factories\Domains\Events\Models\SyncRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncRun extends Model
{
    /** @use HasFactory<SyncRunFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'provider',
        'operation',
        'status',
        'queued_at',
        'started_at',
        'finished_at',
        'events_synced',
        'performances_synced',
        'error_message',
        'context',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SyncRunStatus::class,
            'queued_at' => 'immutable_datetime',
            'started_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
            'events_synced' => 'integer',
            'performances_synced' => 'integer',
            'context' => 'array',
        ];
    }

    protected static function newFactory(): SyncRunFactory
    {
        return SyncRunFactory::new();
    }
}
