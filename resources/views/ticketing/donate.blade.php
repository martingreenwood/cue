@extends('layouts.public', [
    'metaTitle' => 'Donate',
    'metaDescription' => 'Support our work with a secure donation.',
    'canonicalUrl' => route('ticketing.donate'),
])

@section('content')
    <section class="px-5 pb-14 pt-10 sm:px-8 lg:px-12 lg:pt-16">
        <div class="mx-auto max-w-4xl">
            <a href="{{ route('events.index') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-[#a4432e] underline decoration-[#a4432e]/35 underline-offset-4 hover:decoration-[#a4432e] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                Back to events
            </a>

            <header class="mt-8">
                <p class="text-sm font-medium uppercase tracking-[0.24em] text-[#a4432e]">Secure ticketing</p>
                <h1 class="mt-4 text-4xl font-semibold tracking-[-0.05em] sm:text-5xl">Donate</h1>
                <p class="mt-4 text-base leading-7 text-[#5d5549]">Choose a fund and add your donation securely through Spektrix.</p>
            </header>

            <div class="mt-10 space-y-6">
                @foreach ($funds as $fund)
                    <div class="border border-[#171511]/10 bg-white p-6 sm:p-8">
                        <h2 class="text-xl font-semibold text-[#171511]">{{ $fund->name }}</h2>
                        @if ($fund->description !== null && trim($fund->description) !== '')
                            <p class="mt-2 text-sm leading-6 text-[#5d5549]">{{ $fund->description }}</p>
                        @endif
                        <spektrix-donate
                            class="mt-5 block"
                            client-name="{{ $customerSession->clientName }}"
                            @if ($customerSession->customDomain !== null) custom-domain="{{ $customerSession->customDomain }}" @endif
                            fund-id="{{ $fund->external_id }}"
                            donation-amount="{{ (int) (($fund->default_donation_amount_minor ?? 1000) / 100) }}"
                            forward-to="{{ route('ticketing.basket') }}"
                        >
                            <div class="flex flex-wrap gap-2" data-loaded-container>
                                <button type="button" class="inline-flex min-h-10 items-center border border-[#171511]/15 px-3 text-sm font-semibold text-[#171511]" data-set-donation-amount="5">£5</button>
                                <button type="button" class="inline-flex min-h-10 items-center border border-[#171511]/15 px-3 text-sm font-semibold text-[#171511]" data-set-donation-amount="10">£10</button>
                                <button type="button" class="inline-flex min-h-10 items-center border border-[#171511]/15 px-3 text-sm font-semibold text-[#171511]" data-set-donation-amount="25">£25</button>
                                <input type="number" min="1" step="1" data-set-donation-amount-input class="min-h-10 w-28 border border-[#171511]/15 px-3 text-sm" placeholder="Custom">
                                <button type="button" data-submit-donation class="inline-flex min-h-10 items-center bg-[#a4432e] px-4 text-sm font-semibold text-white">Add donation</button>
                            </div>
                            <div data-success-container style="display: none;" class="mt-3 text-sm text-[#5d5549]">Donation added. Continue to basket.</div>
                            <div data-fail-container style="display: none;" class="mt-3 text-sm text-[#7b3021]">Could not add donation. Please try again.</div>
                        </spektrix-donate>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
