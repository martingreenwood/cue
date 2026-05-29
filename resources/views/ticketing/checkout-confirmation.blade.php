@extends('layouts.public', [
    'metaTitle' => 'Booking confirmed',
    'metaDescription' => 'Your booking is confirmed.',
    'canonicalUrl' => route('ticketing.checkout.confirmation'),
])

@section('content')
    <section
        class="px-5 pb-14 pt-10 sm:px-8 lg:px-12 lg:pt-16"
        data-checkout-confirmation
        data-orders-url="{{ $customerSession->ordersUrl }}"
    >
        <div class="mx-auto max-w-3xl">
            <header class="mt-4">
                <p class="text-sm font-medium uppercase tracking-[0.24em] text-[#a4432e]">Secure ticketing</p>
                <h1 class="mt-4 text-4xl font-semibold tracking-[-0.05em] sm:text-5xl">Booking confirmed</h1>
                <p class="mt-4 text-base leading-7 text-[#5d5549]">Thank you for your booking. A confirmation email will be sent to your registered address.</p>
            </header>

            <div data-confirmation-loading class="mt-10 border border-[#171511]/10 bg-white px-6 py-8 text-[#5d5549]">
                Loading your order details.
            </div>

            <div data-confirmation-summary hidden class="mt-10 space-y-4">
                <div data-confirmation-order-details class="border border-[#171511]/10 bg-white p-6 sm:p-8"></div>
            </div>

            <div class="mt-8 flex flex-wrap gap-4">
                <a
                    href="{{ route('ticketing.account.orders') }}"
                    class="inline-flex min-h-12 items-center justify-center bg-[#a4432e] px-8 text-sm font-semibold text-white transition hover:bg-[#873625] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]"
                >
                    View order history
                </a>
                <a
                    href="{{ route('events.index') }}"
                    class="inline-flex min-h-12 items-center justify-center border border-[#171511]/15 px-6 text-sm font-semibold text-[#171511] transition hover:bg-[#f5f0e8] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]"
                >
                    Browse more events
                </a>
            </div>
        </div>
    </section>
@endsection
