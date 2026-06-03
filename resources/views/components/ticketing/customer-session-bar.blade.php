@props(['customerSession', 'siteCopy'])

@if ($customerSession !== null)
    <aside aria-label="Customer account and basket" class="border-b border-[#fdf7ee]/15 bg-[#171511] px-5 text-[#fdf7ee] sm:px-8 lg:px-12">
        <div
            class="mx-auto flex min-h-12 max-w-7xl flex-wrap items-center justify-end gap-x-5 gap-y-2 py-1.5 text-sm"
            data-customer-session-bar
            data-customer-url="{{ $customerSession->customerUrl }}"
        >
            <button
                type="button"
                data-customer-search-toggle
                aria-expanded="false"
                aria-controls="customer-search-panel"
                class="inline-flex min-h-11 items-center gap-2 hover:text-[#e5c8b8] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#fdf7ee]"
            >
                <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" class="size-4">
                    <path d="m14.2 14.2 3.3 3.3M8.8 15.2a6.2 6.2 0 1 1 0-12.4 6.2 6.2 0 0 1 0 12.4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                </svg>
                <span>Search</span>
            </button>

            <span aria-hidden="true" class="h-5 w-px bg-[#fdf7ee]/25"></span>

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
        </div>

        <div
            id="customer-search-panel"
            data-customer-search-panel
            aria-hidden="true"
            class="mx-auto max-h-0 max-w-7xl -translate-y-2 overflow-hidden opacity-0 transition-[max-height,opacity,transform] duration-300 ease-out"
        >
            <form
                method="GET"
                action="{{ route('events.index') }}"
                role="search"
                data-customer-search-form
                data-suggestions-url="{{ route('events.suggestions') }}"
                class="border-t border-[#fdf7ee]/15 pb-5 pt-4"
            >
                <label for="customer-session-search" class="mb-2 block text-sm font-medium text-[#f1dccd]">Search shows and events</label>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input
                        id="customer-session-search"
                        data-customer-search-input
                        type="search"
                        name="q"
                        placeholder="Show, artist or keyword"
                        autocomplete="off"
                        aria-autocomplete="list"
                        aria-controls="customer-search-results"
                        class="block min-h-12 flex-1 rounded-sm border border-[#fdf7ee]/20 bg-[#fffaf2] px-4 py-3 text-[#171511] placeholder:text-[#756d61] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#e5c8b8]"
                    >
                    <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-sm bg-[#fdf7ee] px-6 py-3 font-semibold text-[#171511] transition hover:bg-[#e5c8b8] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#fdf7ee]">
                        View results
                    </button>
                </div>
                <p data-customer-search-status role="status" class="sr-only"></p>
                <div
                    id="customer-search-results"
                    data-customer-search-results
                    hidden
                    class="mt-4 overflow-hidden rounded-sm border border-[#fdf7ee]/15 bg-[#221f1a] shadow-2xl shadow-black/20"
                ></div>
            </form>
        </div>
    </aside>
@endif
