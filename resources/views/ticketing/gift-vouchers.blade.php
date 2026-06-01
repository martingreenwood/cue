@extends('layouts.public', [
    'metaTitle' => 'Gift vouchers',
    'metaDescription' => 'Buy a gift voucher securely through Spektrix.',
    'canonicalUrl' => route('ticketing.gift-vouchers'),
])

@section('content')
    <section class="px-5 pb-14 pt-10 sm:px-8 lg:px-12 lg:pt-16">
        <div class="mx-auto max-w-3xl">
            <a href="{{ route('events.index') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-[#a4432e] underline decoration-[#a4432e]/35 underline-offset-4 hover:decoration-[#a4432e] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                Back to events
            </a>

            <header class="mt-8">
                <p class="text-sm font-medium uppercase tracking-[0.24em] text-[#a4432e]">Secure ticketing</p>
                <h1 class="mt-4 text-4xl font-semibold tracking-[-0.05em] sm:text-5xl">Gift vouchers</h1>
                <p class="mt-4 text-base leading-7 text-[#5d5549]">Purchase and send a gift voucher securely through Spektrix.</p>
            </header>

            <div class="mt-10 border border-[#171511]/10 bg-white p-6 sm:p-8">
                <spektrix-gift-vouchers
                    class="block"
                    client-name="{{ $customerSession->clientName }}"
                    @if ($customerSession->customDomain !== null) custom-domain="{{ $customerSession->customDomain }}" @endif
                    forward-to="{{ route('ticketing.basket') }}"
                >
                    <div class="grid gap-4 sm:grid-cols-2" data-loaded-container>
                        <input type="number" min="1" step="1" placeholder="Amount" data-set-amount class="min-h-12 border border-[#171511]/15 px-4 text-sm">
                        <input type="date" data-send-date class="min-h-12 border border-[#171511]/15 px-4 text-sm">
                        <input type="text" placeholder="Recipient name" data-recipient-name class="min-h-12 border border-[#171511]/15 px-4 text-sm sm:col-span-2">
                        <input type="text" placeholder="Sender name" data-sender-name class="min-h-12 border border-[#171511]/15 px-4 text-sm sm:col-span-2">
                        <textarea placeholder="Message" data-message rows="3" class="border border-[#171511]/15 px-4 py-3 text-sm sm:col-span-2"></textarea>
                        <select data-delivery-type class="min-h-12 border border-[#171511]/15 px-4 text-sm">
                            <option value="email">Email recipient</option>
                            <option value="other-email">Send to another email</option>
                        </select>
                        <input type="email" placeholder="Delivery email address" data-delivery-email class="min-h-12 border border-[#171511]/15 px-4 text-sm">
                        <button type="button" data-submit-gift-voucher class="inline-flex min-h-12 items-center justify-center bg-[#a4432e] px-6 text-sm font-semibold text-white sm:col-span-2">Add gift voucher</button>
                    </div>
                    <div data-success-container style="display: none;" class="mt-3 text-sm text-[#5d5549]">Gift voucher added. Continue to basket.</div>
                    <div data-fail-container style="display: none;" class="mt-3 text-sm text-[#7b3021]">Could not add gift voucher. Please try again.</div>
                </spektrix-gift-vouchers>
            </div>
        </div>
    </section>
@endsection
