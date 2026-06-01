<?php

declare(strict_types=1);

namespace Database\Factories\Domains\Events\Models;

use App\Domains\Events\Models\AvailabilitySnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AvailabilitySnapshot>
 */
class AvailabilitySnapshotFactory extends Factory
{
    /**
     * @var class-string<AvailabilitySnapshot>
     */
    protected $model = AvailabilitySnapshot::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => 'spektrix',
            'sync_run_id' => null,
            'future_on_sale_total' => 0,
            'future_on_sale_available' => 0,
            'future_on_sale_stale' => 0,
            'future_on_sale_unpriced' => 0,
            'captured_at' => now(),
        ];
    }
}
