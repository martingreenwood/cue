@props(['customerSession', 'siteCopy'])

@if ($customerSession !== null)
    <aside aria-label="Customer account and basket" class="border-b border-[#fdf7ee]/15 bg-[#171511] px-5 text-[#fdf7ee] sm:px-8 lg:px-12">
        <div
            class="mx-auto flex min-h-12 max-w-7xl flex-wrap items-center justify-end gap-x-5 gap-y-2 py-1.5 text-sm"
            data-customer-session-bar
            data-customer-url="{{ $customerSession->customerUrl }}"
        >
            <spektrix-login-status
                client-name="{{ $customerSession->clientName }}"
                @if ($customerSession->customDomain !== null) custom-domain="{{ $customerSession->customDomain }}" @endif
                class="!inline-flex min-h-11 items-center"
            >
                <span data-logged-in-container style="display: none;" class="inline-flex min-h-11 items-center">
                    <a href="{{ route('ticketing.account') }}" class="inline-flex min-h-11 items-center hover:text-[#e5c8b8] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#fdf7ee]">
                        {{ $siteCopy->customerLoggedInLabel }}&nbsp;<span data-logged-in-status-customer-first-name></span>
                    </a>
                    <button
                        type="button"
                        data-customer-logout-button
                        data-deauthenticate-url="{{ $customerSession->deauthenticateUrl }}"
                        data-success-url="{{ route('events.index') }}"
                        class="ml-4 inline-flex min-h-11 items-center border-l border-[#fdf7ee]/25 pl-4 hover:text-[#e5c8b8] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#fdf7ee]"
                    >
                        Log out
                    </button>
                </span>
                <span data-logged-out-container class="inline-flex min-h-11 items-center">
                    <a href="{{ route('ticketing.login') }}" class="inline-flex min-h-11 items-center hover:text-[#e5c8b8] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#fdf7ee]">
                        {{ $siteCopy->customerLoggedOutLabel }}
                    </a>
                </span>
            </spektrix-login-status>

            <span aria-hidden="true" class="h-5 w-px bg-[#fdf7ee]/25"></span>

            <spektrix-basket-summary
                client-name="{{ $customerSession->clientName }}"
                @if ($customerSession->customDomain !== null) custom-domain="{{ $customerSession->customDomain }}" @endif
                class="!inline-flex min-h-11 items-center"
            >
                <a href="{{ route('ticketing.basket') }}" class="inline-flex min-h-11 items-center hover:text-[#e5c8b8] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#fdf7ee]">
                    {{ $siteCopy->customerBasketLabel }} (<span data-basket-item-count>0</span>)
                </a>
            </spektrix-basket-summary>

            <span aria-hidden="true" class="h-5 w-px bg-[#fdf7ee]/25"></span>

            <a href="{{ route('ticketing.donate') }}" class="inline-flex min-h-11 items-center hover:text-[#e5c8b8] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#fdf7ee]">
                {{ $siteCopy->customerDonateLabel }}
            </a>

            <a href="{{ route('ticketing.gift-vouchers') }}" class="inline-flex min-h-11 items-center hover:text-[#e5c8b8] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#fdf7ee]">
                {{ $siteCopy->customerGiftVouchersLabel }}
            </a>

            <a href="{{ route('ticketing.memberships') }}" class="inline-flex min-h-11 items-center hover:text-[#e5c8b8] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#fdf7ee]">
                {{ $siteCopy->customerMembershipsLabel }}
            </a>
        </div>
    </aside>
@endif
