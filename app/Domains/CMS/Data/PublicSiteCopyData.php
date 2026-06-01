<?php

declare(strict_types=1);

namespace App\Domains\CMS\Data;

final readonly class PublicSiteCopyData
{
    public function __construct(
        public string $listingKicker,
        public string $guidePriceLabel,
        public string $guidePricePrefix,
        public string $pricesConfirmedInBooking,
        public string $dynamicPriceSuffix,
        public string $stalePriceSuffix,
        public string $performanceFreshnessNotice,
        public string $bookingCtaLabel,
        public string $onlineBookingUnavailableLabel,
        public string $secureBookingPrefix,
        public string $footerAvailabilityNotice,
        public string $customerLoggedInLabel,
        public string $customerLoggedOutLabel,
        public string $customerBasketLabel,
        public string $basketMembershipUpsell,
        public string $customerDonateLabel,
        public string $customerGiftVouchersLabel,
        public string $customerMembershipsLabel,
    ) {}

    /**
     * @return array{
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
    public function toArray(): array
    {
        return [
            'listing_kicker' => $this->listingKicker,
            'guide_price_label' => $this->guidePriceLabel,
            'guide_price_prefix' => $this->guidePricePrefix,
            'prices_confirmed_in_booking' => $this->pricesConfirmedInBooking,
            'dynamic_price_suffix' => $this->dynamicPriceSuffix,
            'stale_price_suffix' => $this->stalePriceSuffix,
            'performance_freshness_notice' => $this->performanceFreshnessNotice,
            'booking_cta_label' => $this->bookingCtaLabel,
            'online_booking_unavailable_label' => $this->onlineBookingUnavailableLabel,
            'secure_booking_prefix' => $this->secureBookingPrefix,
            'footer_availability_notice' => $this->footerAvailabilityNotice,
            'customer_logged_in_label' => $this->customerLoggedInLabel,
            'customer_logged_out_label' => $this->customerLoggedOutLabel,
            'customer_basket_label' => $this->customerBasketLabel,
            'basket_membership_upsell' => $this->basketMembershipUpsell,
            'customer_donate_label' => $this->customerDonateLabel,
            'customer_gift_vouchers_label' => $this->customerGiftVouchersLabel,
            'customer_memberships_label' => $this->customerMembershipsLabel,
        ];
    }
}
