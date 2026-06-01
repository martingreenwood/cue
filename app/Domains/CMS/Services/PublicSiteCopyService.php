<?php

declare(strict_types=1);

namespace App\Domains\CMS\Services;

use App\Domains\CMS\Data\PublicSiteCopyData;
use App\Domains\CMS\Models\PublicSiteSetting;

final class PublicSiteCopyService
{
    /**
     * @var array{
     *     listing_kicker: string,
     *     guide_price_label: string,
     *     guide_price_prefix: string,
     *     prices_confirmed_in_booking: string,
     *     dynamic_price_suffix: string,
     *     stale_price_suffix: string,
     *     performance_freshness_notice: string,
     *     booking_cta_label: string,
     *     online_booking_unavailable_label: string,
     *     secure_booking_prefix: string,
     *     footer_availability_notice: string,
     *     customer_logged_in_label: string,
     *     customer_logged_out_label: string,
     *     customer_basket_label: string,
     *     basket_membership_upsell: string,
     *     customer_donate_label: string,
     *     customer_gift_vouchers_label: string,
     *     customer_memberships_label: string
     * }
     */
    private const array Defaults = [
        'listing_kicker' => 'Upcoming events',
        'guide_price_label' => 'Guide price',
        'guide_price_prefix' => 'Guide price from',
        'prices_confirmed_in_booking' => 'Prices confirmed in booking',
        'dynamic_price_suffix' => 'May change',
        'stale_price_suffix' => 'Check in booking',
        'performance_freshness_notice' => 'Performance dates and guide prices are shown from our latest catalogue update. Check current availability and final prices during secure booking.',
        'booking_cta_label' => 'Check availability and book',
        'online_booking_unavailable_label' => 'Online booking unavailable',
        'secure_booking_prefix' => 'Check current availability and final prices securely with Spektrix for',
        'footer_availability_notice' => 'Current ticket availability and final prices are confirmed during secure booking.',
        'customer_logged_in_label' => 'Signed in as',
        'customer_logged_out_label' => 'Log in',
        'customer_basket_label' => 'Basket',
        'basket_membership_upsell' => 'If you hold a membership, log in and any eligible discounts will be applied to your order automatically.',
        'customer_donate_label' => 'Donate',
        'customer_gift_vouchers_label' => 'Gift vouchers',
        'customer_memberships_label' => 'Memberships',
    ];

    public function current(): PublicSiteCopyData
    {
        $settings = PublicSiteSetting::query()->find(1);

        return new PublicSiteCopyData(
            listingKicker: $this->value($settings?->listing_kicker, 'listing_kicker'),
            guidePriceLabel: $this->value($settings?->guide_price_label, 'guide_price_label'),
            guidePricePrefix: $this->value($settings?->guide_price_prefix, 'guide_price_prefix'),
            pricesConfirmedInBooking: $this->value($settings?->prices_confirmed_in_booking, 'prices_confirmed_in_booking'),
            dynamicPriceSuffix: $this->value($settings?->dynamic_price_suffix, 'dynamic_price_suffix'),
            stalePriceSuffix: $this->value($settings?->stale_price_suffix, 'stale_price_suffix'),
            performanceFreshnessNotice: $this->value($settings?->performance_freshness_notice, 'performance_freshness_notice'),
            bookingCtaLabel: $this->value($settings?->booking_cta_label, 'booking_cta_label'),
            onlineBookingUnavailableLabel: $this->value($settings?->online_booking_unavailable_label, 'online_booking_unavailable_label'),
            secureBookingPrefix: $this->value($settings?->secure_booking_prefix, 'secure_booking_prefix'),
            footerAvailabilityNotice: $this->value($settings?->footer_availability_notice, 'footer_availability_notice'),
            customerLoggedInLabel: $this->value($settings?->customer_logged_in_label, 'customer_logged_in_label'),
            customerLoggedOutLabel: $this->value($settings?->customer_logged_out_label, 'customer_logged_out_label'),
            customerBasketLabel: $this->value($settings?->customer_basket_label, 'customer_basket_label'),
            basketMembershipUpsell: $this->value($settings?->basket_membership_upsell, 'basket_membership_upsell'),
            customerDonateLabel: $this->value($settings?->customer_donate_label, 'customer_donate_label'),
            customerGiftVouchersLabel: $this->value($settings?->customer_gift_vouchers_label, 'customer_gift_vouchers_label'),
            customerMembershipsLabel: $this->value($settings?->customer_memberships_label, 'customer_memberships_label'),
        );
    }

    /**
     * @param  array{
     *     listing_kicker: string,
     *     guide_price_label: string,
     *     guide_price_prefix: string,
     *     prices_confirmed_in_booking: string,
     *     dynamic_price_suffix: string,
     *     stale_price_suffix: string,
     *     performance_freshness_notice: string,
     *     booking_cta_label: string,
     *     online_booking_unavailable_label: string,
     *     secure_booking_prefix: string,
     *     footer_availability_notice: string,
     *     customer_logged_in_label: string,
     *     customer_logged_out_label: string,
     *     customer_basket_label: string,
     *     basket_membership_upsell: string,
     *     customer_donate_label: string,
     *     customer_gift_vouchers_label: string,
     *     customer_memberships_label: string
     * }  $data
     */
    public function update(array $data): PublicSiteSetting
    {
        return PublicSiteSetting::query()->updateOrCreate(['id' => 1], $data);
    }

    /**
     * @param  key-of<self::Defaults>  $key
     */
    private function value(?string $value, string $key): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : self::Defaults[$key];
    }
}
