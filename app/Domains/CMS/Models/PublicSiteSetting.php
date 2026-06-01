<?php

declare(strict_types=1);

namespace App\Domains\CMS\Models;

use Database\Factories\Domains\CMS\Models\PublicSiteSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicSiteSetting extends Model
{
    /** @use HasFactory<PublicSiteSettingFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'listing_kicker',
        'guide_price_label',
        'guide_price_prefix',
        'prices_confirmed_in_booking',
        'dynamic_price_suffix',
        'stale_price_suffix',
        'performance_freshness_notice',
        'booking_cta_label',
        'online_booking_unavailable_label',
        'secure_booking_prefix',
        'footer_availability_notice',
        'customer_logged_in_label',
        'customer_logged_out_label',
        'customer_basket_label',
        'basket_membership_upsell',
        'customer_donate_label',
        'customer_gift_vouchers_label',
        'customer_memberships_label',
    ];

    protected static function newFactory(): PublicSiteSettingFactory
    {
        return PublicSiteSettingFactory::new();
    }
}
