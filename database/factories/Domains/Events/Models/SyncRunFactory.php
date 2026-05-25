<?php

declare(strict_types=1);

namespace Database\Factories\Domains\Events\Models;

use App\Domains\Events\Enums\SyncRunStatus;
use App\Domains\Events\Models\SyncRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SyncRun>
 */
class SyncRunFactory extends Factory
{
    /**
     * @var class-string<SyncRun>
     */
    protected $model = SyncRun::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => 'spektrix',
            'operation' => 'catalogue',
            'status' => SyncRunStatus::Queued,
            'queued_at' => now(),
            'events_synced' => 0,
            'performances_synced' => 0,
            'performances_queued' => 0,
            'performances_failed' => 0,
            'prices_synced' => 0,
        ];
    }
}
