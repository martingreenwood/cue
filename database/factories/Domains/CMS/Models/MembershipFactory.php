<?php

declare(strict_types=1);

namespace Database\Factories\Domains\CMS\Models;

use App\Domains\CMS\Models\Membership;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * @var class-string<Membership>
     */
    protected $model = Membership::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => 'spektrix',
            'external_id' => fake()->unique()->bothify('membership-########'),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'html_description' => '<p>'.fake()->sentence().'</p>',
            'image_url' => fake()->imageUrl(),
            'thumbnail_url' => fake()->imageUrl(),
            'is_visible' => true,
            'sort_order' => 0,
            'source_payload' => [],
            'synced_at' => now(),
        ];
    }
}
