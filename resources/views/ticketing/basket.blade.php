@extends('layouts.public', [
    'metaTitle' => 'Your basket',
    'metaDescription' => 'Review your tickets and continue securely to checkout.',
    'canonicalUrl' => route('ticketing.basket'),
])

@section('content')
    <section
        class="px-5 pb-14 pt-10 sm:px-8 lg:px-12 lg:pt-16"
        data-customer-basket
        data-basket-url="{{ $customerSession->basketUrl }}"
        data-basket-tickets-url="{{ $customerSession->basketTicketsUrl }}"
        data-basket-merchandise-url="{{ $customerSession->basketMerchandiseUrl }}"
        data-stock-items-url="{{ $customerSession->stockItemsUrl }}"
        data-customer-url="{{ $customerSession->customerUrl }}"
        data-checkout-url="{{ route('ticketing.checkout') }}"
        data-login-url="{{ route('ticketing.login') }}"
        data-client-name="{{ $customerSession->clientName }}"
        data-custom-domain="{{ $customerSession->customDomain ?? '' }}"
        data-membership-upsell="{{ $siteCopy->basketMembershipUpsell }}"
    >
        <div class="mx-auto max-w-3xl">
            <a href="{{ route('events.index') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-[#a4432e] underline decoration-[#a4432e]/35 underline-offset-4 hover:decoration-[#a4432e] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                Back to events
            </a>

            <header class="mt-8">
                <p class="text-sm font-medium uppercase tracking-[0.24em] text-[#a4432e]">Secure ticketing</p>
                <h1 class="mt-4 text-4xl font-semibold tracking-[-0.05em] sm:text-5xl">Your basket</h1>
            </header>

            <div data-basket-loading class="mt-10 border border-[#171511]/10 bg-white px-6 py-8 text-[#5d5549]">
                Loading your basket.
            </div>

            <div data-basket-error hidden class="mt-10 border border-[#171511]/10 bg-white px-6 py-8 text-[#5d5549]">
                We could not load your basket. Please try again or <a href="{{ route('events.index') }}" class="font-medium text-[#a4432e] underline underline-offset-4 hover:text-[#873625]">return to events</a>.
            </div>

            <div data-basket-empty hidden class="mt-10 border border-[#171511]/10 bg-white px-6 py-8">
                <p class="text-[#5d5549]">Your basket is empty.</p>
                <a href="{{ route('events.index') }}" class="mt-5 inline-flex min-h-12 items-center justify-center bg-[#a4432e] px-6 text-sm font-semibold text-white transition hover:bg-[#873625] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                    Browse events
                </a>
            </div>

            <div data-basket-content hidden>
                {{-- Savings: rendered by JS when PotentialOffers or MultibuyOffers are present --}}
                <div data-basket-savings hidden class="mt-10 border border-[#171511]/10 bg-white p-6 sm:p-8">
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-[#a4432e]">Savings</p>
                    <div data-basket-offers class="mt-4 space-y-2 text-sm leading-6 text-[#5d5549]"></div>
                    <form data-basket-promo-form class="mt-6 flex flex-wrap items-end gap-3">
                        <div class="flex flex-col gap-2">
                            <label for="basket-promo-code" class="text-sm font-medium text-[#171511]">Promotion code</label>
                            <input
                                id="basket-promo-code"
                                name="promoCode"
                                type="text"
                                autocomplete="off"
                                class="min-h-12 border border-[#171511]/15 bg-white px-4 text-base focus:border-[#a4432e] focus:outline-none focus:ring-2 focus:ring-[#a4432e]/20"
                                placeholder="Enter code"
                            >
                        </div>
                        <button
                            type="submit"
                            data-basket-promo-submit
                            class="inline-flex min-h-12 items-center justify-center border border-[#171511]/15 px-6 text-sm font-semibold text-[#171511] transition hover:bg-[#f5f0e8] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e] disabled:cursor-wait disabled:opacity-70"
                        >
                            Apply code
                        </button>
                        <p data-basket-promo-feedback tabindex="-1" role="status" hidden class="w-full text-sm text-[#5d5549]"></p>
                        <p data-basket-promo-error tabindex="-1" role="alert" hidden class="w-full text-sm text-[#7b3021]"></p>
                    </form>
                </div>

                {{-- Membership upsell: rendered by JS when basket.Customer is null --}}
                <div data-basket-membership-upsell hidden class="mt-6 border border-[#171511]/10 bg-[#f5f0e8] px-5 py-4 text-sm leading-6 text-[#5d5549]">
                    <span data-basket-membership-upsell-copy></span>
                    <a data-basket-login-url href="#" class="ml-1 font-medium text-[#a4432e] underline underline-offset-4 hover:text-[#873625]">Log in</a>
                </div>

                {{-- Ticket list --}}
                <div class="mt-8">
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-[#a4432e]">Tickets</p>
                    <div data-basket-tickets class="mt-4 space-y-4"></div>
                </div>

                {{-- Totals --}}
                <div data-basket-totals class="mt-6 border-t border-[#171511]/10 pt-6 text-sm text-[#5d5549]">
                </div>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a
                        data-basket-checkout-link
                        href="{{ route('ticketing.checkout') }}"
                        class="inline-flex min-h-12 items-center justify-center bg-[#a4432e] px-8 text-sm font-semibold text-white transition hover:bg-[#873625] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]"
                    >
                        Continue to checkout
                    </a>
                    <a href="{{ route('events.index') }}" class="inline-flex min-h-12 items-center justify-center border border-[#171511]/15 px-6 text-sm font-semibold text-[#171511] transition hover:bg-[#f5f0e8] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                        Continue browsing
                    </a>
                </div>

                {{-- Merchandise upsell: rendered by JS when stock items have availability --}}
                <div data-basket-merchandise hidden class="mt-12">
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-[#a4432e]">You may also be interested in</p>
                    <div data-basket-merchandise-items class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"></div>
                </div>
            </div>
        </div>
    </section>
@endsection
