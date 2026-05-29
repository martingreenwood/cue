@extends('layouts.public', [
    'metaTitle' => 'Checkout',
    'metaDescription' => 'Complete your booking securely.',
    'canonicalUrl' => route('ticketing.checkout'),
])

@push('head')
    <link rel="stylesheet" href="https://webcomponents.spektrix.com/stable/spektrix-payments.css">
    <script src="https://webcomponents.spektrix.com/stable/spektrix-payments.js" defer></script>
@endpush

@section('content')
    <section
        class="px-5 pb-14 pt-10 sm:px-8 lg:px-12 lg:pt-16"
        data-customer-checkout
        data-customer-url="{{ $customerSession->customerUrl }}"
        data-addresses-url="{{ $customerSession->addressesUrl }}"
        data-basket-url="{{ $customerSession->basketUrl }}"
        data-initiate-direct-payment-url="{{ $customerSession->initiateDirectPaymentUrl }}"
        data-initiate-customer-payment-url="{{ $customerSession->initiateCustomerPaymentUrl }}"
        data-basket-url-return="{{ route('ticketing.basket') }}"
        data-confirmation-url="{{ route('ticketing.checkout.confirmation') }}"
        data-client-name="{{ $customerSession->clientName }}"
        data-custom-domain="{{ $customerSession->customDomain ?? '' }}"
    >
        <div class="mx-auto max-w-3xl">
            <a href="{{ route('ticketing.basket') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-[#a4432e] underline decoration-[#a4432e]/35 underline-offset-4 hover:decoration-[#a4432e] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                Back to basket
            </a>

            <header class="mt-8">
                <p class="text-sm font-medium uppercase tracking-[0.24em] text-[#a4432e]">Secure ticketing</p>
                <h1 class="mt-4 text-4xl font-semibold tracking-[-0.05em] sm:text-5xl">Checkout</h1>
                <p class="mt-4 text-base leading-7 text-[#5d5549]">Complete your booking securely. Your payment is processed by Spektrix and Adyen — your card details are never handled by Cue.</p>
            </header>

            <div data-checkout-loading class="mt-10 border border-[#171511]/10 bg-white px-6 py-8 text-[#5d5549]">
                Preparing secure payment.
            </div>

            <div data-checkout-error hidden class="mt-10 border border-[#171511]/10 bg-white px-6 py-8 text-[#5d5549]">
                <p>We could not start the checkout. <a href="{{ route('ticketing.basket') }}" class="font-medium text-[#a4432e] underline underline-offset-4 hover:text-[#873625]">Return to your basket</a> and try again.</p>
            </div>

            <div data-checkout-expired hidden class="mt-10 border border-[#171511]/10 bg-white px-6 py-8 text-[#5d5549]">
                <p>Your basket session has expired. <a href="{{ route('ticketing.basket') }}" class="font-medium text-[#a4432e] underline underline-offset-4 hover:text-[#873625]">Return to your basket</a> to start again.</p>
            </div>

            <div data-checkout-refused hidden class="mt-10 border border-[#171511]/10 bg-white px-6 py-8">
                <p class="text-[#5d5549]">Your payment was not accepted. Please check your card details and try again.</p>
                <button
                    type="button"
                    data-checkout-retry
                    class="mt-5 inline-flex min-h-12 items-center justify-center border border-[#171511]/15 px-6 text-sm font-semibold text-[#171511] transition hover:bg-[#f5f0e8] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]"
                >
                    Try again
                </button>
            </div>

            <div data-checkout-payment hidden class="mt-10 border border-[#171511]/10 bg-white p-6 sm:p-8">
                <spektrix-payments
                    id="spektrixPayments"
                    custom-domain="{{ $customerSession->customDomain ?? '' }}"
                    system-name="{{ $customerSession->clientName }}"
                ></spektrix-payments>
            </div>
        </div>
    </section>
@endsection
