<?php

declare(strict_types=1);

namespace Database\Factories\Domains\CMS\Models;

use App\Domains\CMS\Models\PublicSiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PublicSiteSetting>
 */
class PublicSiteSettingFactory extends Factory
{
    /**
     * @var class-string<PublicSiteSetting>
     */
    protected $model = PublicSiteSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'listing_kicker' => null,
            'guide_price_label' => null,
            'guide_price_prefix' => null,
            'prices_confirmed_in_booking' => null,
            'dynamic_price_suffix' => null,
            'stale_price_suffix' => null,
            'performance_freshness_notice' => null,
            'booking_cta_label' => null,
            'online_booking_unavailable_label' => null,
            'secure_booking_prefix' => null,
            'footer_availability_notice' => null,
            'customer_logged_in_label' => null,
            'customer_logged_out_label' => null,
            'customer_basket_label' => null,
        ];
    }
}
