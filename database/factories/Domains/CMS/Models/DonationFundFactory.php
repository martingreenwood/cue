<?php

declare(strict_types=1);

namespace Database\Factories\Domains\CMS\Models;

use App\Domains\CMS\Models\DonationFund;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DonationFund>
 */
class DonationFundFactory extends Factory
{
    /**
     * @var class-string<DonationFund>
     */
    protected $model = DonationFund::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => 'spektrix',
            'external_id' => fake()->unique()->bothify('fund-########'),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'code' => strtoupper(fake()->lexify('???')),
            'default_donation_amount_minor' => fake()->randomElement([500, 1000, 2500]),
            'is_visible' => true,
            'sort_order' => 0,
            'source_payload' => [],
            'synced_at' => now(),
        ];
    }
}
