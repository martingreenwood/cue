@extends('layouts.public', [
    'metaTitle' => 'Memberships',
    'metaDescription' => 'Buy a membership securely through Spektrix.',
    'canonicalUrl' => route('ticketing.memberships'),
])

@section('content')
    <section class="px-5 pb-14 pt-10 sm:px-8 lg:px-12 lg:pt-16">
        <div class="mx-auto max-w-4xl">
            <a href="{{ route('events.index') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-[#a4432e] underline decoration-[#a4432e]/35 underline-offset-4 hover:decoration-[#a4432e] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                Back to events
            </a>

            <header class="mt-8">
                <p class="text-sm font-medium uppercase tracking-[0.24em] text-[#a4432e]">Secure ticketing</p>
                <h1 class="mt-4 text-4xl font-semibold tracking-[-0.05em] sm:text-5xl">Memberships</h1>
                <p class="mt-4 text-base leading-7 text-[#5d5549]">Join a membership and continue to basket securely through Spektrix.</p>
            </header>

            <div class="mt-10 space-y-6">
                @foreach ($memberships as $membership)
                    <div class="border border-[#171511]/10 bg-white p-6 sm:p-8">
                        <spektrix-memberships
                            class="block"
                            client-name="{{ $customerSession->clientName }}"
                            @if ($customerSession->customDomain !== null) custom-domain="{{ $customerSession->customDomain }}" @endif
                            membership-id="{{ $membership->external_id }}"
                            forward-to="{{ route('ticketing.basket') }}"
                        >
                            <div data-loading-container class="text-sm text-[#5d5549]">Loading membership details.</div>
                            <div data-loaded-container style="display: none;">
                                <h2 class="text-xl font-semibold text-[#171511]" data-name></h2>
                                <p class="mt-2 text-sm leading-6 text-[#5d5549]" data-description></p>
                                <p class="mt-2 text-sm font-semibold text-[#171511]" data-price></p>
                                <p class="mt-1 text-sm text-[#5d5549]" data-renewal-price></p>
                                <div data-is-variable-membership style="display: none;" class="mt-3">
                                    <input type="number" min="1" step="1" data-variable-price-input class="min-h-10 w-40 border border-[#171511]/15 px-3 text-sm" placeholder="Your price">
                                </div>
                                <label class="mt-4 inline-flex items-center gap-2 text-sm text-[#5d5549]">
                                    <input type="checkbox" data-set-autorenew class="size-4 accent-[#a4432e]">
                                    Auto-renew
                                </label>
                                <p data-customer-holds-membership style="display: none;" class="mt-3 text-sm text-[#5d5549]">You already hold this membership.</p>
                                <p data-customer-does-not-hold-membership style="display: none;" class="mt-3 text-sm text-[#5d5549]">Available to purchase now.</p>
                                <button type="button" data-submit-membership class="mt-4 inline-flex min-h-10 items-center bg-[#a4432e] px-4 text-sm font-semibold text-white">Add membership</button>
                            </div>
                            <div data-success-container style="display: none;" class="mt-3 text-sm text-[#5d5549]">Membership added. Continue to basket.</div>
                            <div data-fail-container style="display: none;" class="mt-3 text-sm text-[#7b3021]">Could not add membership. Please try again.</div>
                        </spektrix-memberships>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
