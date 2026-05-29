@php
    $accountSections = [
        'profile' => ['label' => 'Profile', 'route' => 'ticketing.account.profile'],
        'addresses' => ['label' => 'Addresses', 'route' => 'ticketing.account.addresses'],
        'orders' => ['label' => 'Orders', 'route' => 'ticketing.account.orders'],
        'payments' => ['label' => 'Payments', 'route' => 'ticketing.account.payments'],
        'security' => ['label' => 'Security', 'route' => 'ticketing.account.security'],
        'contact-preferences' => ['label' => 'Contact preferences', 'route' => 'ticketing.account.contact-preferences'],
    ];
@endphp

@extends('layouts.public', [
    'metaTitle' => 'My account',
    'metaDescription' => 'Manage your ticketing account profile, orders, addresses, contact preferences and saved cards.',
    'canonicalUrl' => route($accountSections[$activeSection]['route']),
])

@section('content')
    <section
        class="px-5 py-16 sm:px-8 lg:px-12"
        data-customer-account
        data-customer-url="{{ $customerSession->customerUrl }}"
        data-update-customer-url="{{ $customerSession->updateCustomerUrl }}"
        data-statements-url="{{ $customerSession->statementsUrl }}"
        data-agreed-statements-url="{{ $customerSession->agreedStatementsUrl }}"
        data-addresses-url="{{ $customerSession->addressesUrl }}"
        data-countries-url="{{ $customerSession->countriesUrl }}"
        data-postcode-lookup-url="{{ $customerSession->postcodeLookupUrl }}"
        data-orders-url="{{ $customerSession->ordersUrl }}"
        data-print-at-home-documents-url="{{ $customerSession->printAtHomeDocumentsUrl }}"
        data-stored-cards-url="{{ $customerSession->storedCardsUrl }}"
        data-change-password-url="{{ $customerSession->changePasswordUrl }}"
        data-forgot-password-url="{{ $customerSession->forgotPasswordUrl }}"
        data-deauthenticate-url="{{ $customerSession->deauthenticateUrl }}"
        data-login-url="{{ route('ticketing.login') }}"
        data-password-reset-requested-url="{{ route('ticketing.login', ['password_reset' => 'requested']) }}"
    >
        <div class="mx-auto max-w-7xl">
            <a href="{{ route('events.index') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-[#a4432e] underline underline-offset-4 hover:text-[#873625] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                Back to events
            </a>

            <div class="mt-10 max-w-3xl">
                <p class="text-sm font-medium uppercase tracking-[0.24em] text-[#a4432e]">Your account</p>
                <h1 class="mt-4 text-5xl font-semibold tracking-[-0.06em] sm:text-6xl">My account</h1>
                <p class="mt-5 text-lg leading-8 text-[#5d5549]">
                    View your Spektrix ticketing profile, order history, saved addresses, contact preferences and stored payment cards without leaving Cue.
                </p>
            </div>

            <div data-account-loading class="mt-12 border border-[#171511]/10 bg-white px-6 py-8 text-[#5d5549] shadow-sm">
                Checking your secure ticketing account.
            </div>

            <div data-account-signed-out hidden class="mt-12 max-w-2xl border border-[#171511]/10 bg-white p-8 shadow-sm">
                <h2 class="text-3xl font-semibold tracking-[-0.04em]">Sign in to continue</h2>
                <p class="mt-4 leading-7 text-[#5d5549]">
                    Your account details are protected by Spektrix. Sign in to view your profile, orders, addresses, contact preferences and stored cards.
                </p>
                <a href="{{ route('ticketing.login') }}" class="mt-6 inline-flex min-h-12 items-center justify-center bg-[#a4432e] px-6 text-sm font-semibold text-white transition hover:bg-[#873625] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                    Log in
                </a>
            </div>

            <div data-account-signed-in hidden class="mt-12 grid gap-8 lg:grid-cols-[13rem_minmax(0,1fr)] lg:items-start">
                <nav aria-label="Account sections" class="lg:sticky lg:top-6">
                    <div class="flex gap-2 overflow-x-auto border border-[#171511]/10 bg-white p-2 shadow-sm lg:flex-col lg:overflow-visible">
                        @foreach ($accountSections as $sectionKey => $section)
                            <a
                                href="{{ route($section['route']) }}"
                                @class([
                                    'inline-flex min-h-11 shrink-0 items-center border px-4 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a4432e]',
                                    'border-[#a4432e]/30 bg-[#f5f0e8] text-[#171511]' => $activeSection === $sectionKey,
                                    'border-transparent text-[#5d5549] hover:border-[#171511]/10 hover:bg-[#f5f0e8] hover:text-[#171511]' => $activeSection !== $sectionKey,
                                ])
                                @if ($activeSection === $sectionKey) aria-current="page" @endif
                            >
                                {{ $section['label'] }}
                            </a>
                        @endforeach
                    </div>
                </nav>

                <div>
                    @if ($activeSection === 'profile')
                        <section class="border border-[#171511]/10 bg-white p-6 shadow-sm sm:p-8">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-[#a4432e]">Profile</p>
                                    <h2 class="mt-3 text-3xl font-semibold tracking-[-0.04em]" data-account-customer-name>Signed in</h2>
                                </div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <span data-account-password-state class="inline-flex min-h-10 items-center self-start border border-[#171511]/10 px-3 text-xs font-semibold uppercase tracking-[0.16em] text-[#5d5549]"></span>
                                    <button type="button" data-account-profile-edit-button class="inline-flex min-h-10 items-center justify-center bg-[#a4432e] px-4 text-sm font-semibold text-white transition hover:bg-[#873625] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                                        Edit profile
                                    </button>
                                </div>
                            </div>

                            <dl data-account-profile-summary class="mt-8 grid gap-5 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-[#5d5549]">Title</dt>
                                    <dd class="mt-1 text-lg" data-account-customer-title>Not supplied</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-[#5d5549]">First name</dt>
                                    <dd class="mt-1 text-lg" data-account-customer-first-name>Not supplied</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-[#5d5549]">Last name</dt>
                                    <dd class="mt-1 text-lg" data-account-customer-last-name>Not supplied</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-[#5d5549]">Email</dt>
                                    <dd class="mt-1 text-lg" data-account-customer-email>Not supplied</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-[#5d5549]">Phone</dt>
                                    <dd class="mt-1 text-lg" data-account-customer-phone>Not supplied</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-[#5d5549]">Mobile</dt>
                                    <dd class="mt-1 text-lg" data-account-customer-mobile>Not supplied</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-[#5d5549]">Date of birth</dt>
                                    <dd class="mt-1 text-lg" data-account-customer-birth-date>Not supplied</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-[#5d5549]">Gift Aid</dt>
                                    <dd class="mt-1 text-lg" data-account-customer-gift-aid>Not supplied</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-[#5d5549]">Credit balance</dt>
                                    <dd class="mt-1 text-lg" data-account-credit-balance>Not supplied</dd>
                                </div>
                            </dl>

                            <form method="post" action="{{ $customerSession->updateCustomerUrl }}" data-account-profile-form hidden class="mt-8 border-t border-[#171511]/10 pt-8">
                                <h3 class="text-xl font-semibold tracking-[-0.03em]">Update profile</h3>
                                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                    <div class="flex flex-col gap-2">
                                        <label for="account-title" class="text-sm font-medium text-[#171511]">Title</label>
                                        <input id="account-title" name="title" autocomplete="honorific-prefix" class="min-h-12 border border-[#171511]/15 bg-white px-4 text-base focus:border-[#a4432e] focus:outline-none focus:ring-2 focus:ring-[#a4432e]/20">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label for="account-first-name" class="text-sm font-medium text-[#171511]">First name</label>
                                        <input id="account-first-name" name="firstName" autocomplete="given-name" class="min-h-12 border border-[#171511]/15 bg-white px-4 text-base focus:border-[#a4432e] focus:outline-none focus:ring-2 focus:ring-[#a4432e]/20">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label for="account-last-name" class="text-sm font-medium text-[#171511]">Last name</label>
                                        <input id="account-last-name" name="lastName" autocomplete="family-name" class="min-h-12 border border-[#171511]/15 bg-white px-4 text-base focus:border-[#a4432e] focus:outline-none focus:ring-2 focus:ring-[#a4432e]/20">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label for="account-email" class="text-sm font-medium text-[#171511]">Email</label>
                                        <input id="account-email" name="email" type="email" autocomplete="email" class="min-h-12 border border-[#171511]/15 bg-white px-4 text-base focus:border-[#a4432e] focus:outline-none focus:ring-2 focus:ring-[#a4432e]/20">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label for="account-phone" class="text-sm font-medium text-[#171511]">Phone</label>
                                        <input id="account-phone" name="phone" autocomplete="tel" class="min-h-12 border border-[#171511]/15 bg-white px-4 text-base focus:border-[#a4432e] focus:outline-none focus:ring-2 focus:ring-[#a4432e]/20">
                                    </div>
                                    <div class="flex flex-col gap-2 sm:col-span-2">
                                        <label for="account-mobile" class="text-sm font-medium text-[#171511]">Mobile</label>
                                        <input id="account-mobile" name="mobile" autocomplete="tel" class="min-h-12 border border-[#171511]/15 bg-white px-4 text-base focus:border-[#a4432e] focus:outline-none focus:ring-2 focus:ring-[#a4432e]/20">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label for="account-birth-date" class="text-sm font-medium text-[#171511]">Date of birth</label>
                                        <input id="account-birth-date" name="birthDate" type="date" autocomplete="bday" class="min-h-12 border border-[#171511]/15 bg-white px-4 text-base focus:border-[#a4432e] focus:outline-none focus:ring-2 focus:ring-[#a4432e]/20">
                                    </div>
                                    <label class="flex min-h-12 items-center gap-3 self-end border border-[#171511]/10 bg-[#f5f0e8] px-4 text-sm font-medium text-[#171511]">
                                        <input name="giftAidConfirmed" type="checkbox" value="1" class="size-4 accent-[#a4432e]">
                                        Gift Aid confirmed
                                    </label>
                                </div>
                                <p data-account-profile-feedback tabindex="-1" role="status" hidden class="mt-5 border border-[#a4432e]/25 bg-[#f5f0e8] px-4 py-3 text-sm leading-6 text-[#5d5549]">
                                    Profile updated.
                                </p>
                                <p data-account-profile-error tabindex="-1" role="alert" hidden class="mt-5 border border-[#a4432e]/25 bg-[#f5f0e8] px-4 py-3 text-sm leading-6 text-[#7b3021]">
                                    We could not update your profile. Please check your details and try again.
                                </p>
                                <div class="mt-5 flex flex-wrap gap-3">
                                    <button type="submit" data-account-profile-submit class="inline-flex min-h-12 items-center justify-center bg-[#a4432e] px-6 text-sm font-semibold text-white transition hover:bg-[#873625] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e] disabled:cursor-wait disabled:opacity-70">
                                        Save profile
                                    </button>
                                    <button type="button" data-account-profile-cancel-button class="inline-flex min-h-12 items-center justify-center border border-[#171511]/15 px-6 text-sm font-semibold text-[#171511] transition hover:bg-[#f5f0e8] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </section>
                    @elseif ($activeSection === 'addresses')
                        <section class="border border-[#171511]/10 bg-white p-6 shadow-sm sm:p-8">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-[#a4432e]">Addresses</p>
                                    <h2 class="mt-3 text-3xl font-semibold tracking-[-0.04em]">Saved addresses</h2>
                                </div>
                                <button type="button" data-account-address-new-button class="inline-flex min-h-11 items-center justify-center bg-[#a4432e] px-4 text-sm font-semibold text-white transition hover:bg-[#873625] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                                    Add address
                                </button>
                            </div>
                            <p data-account-addresses-status class="mt-5 text-sm leading-6 text-[#5d5549]">Loading saved addresses.</p>
                            <div data-account-addresses class="mt-6 space-y-4 text-[#5d5549]"></div>
                            <form method="post" action="{{ $customerSession->addressesUrl }}" data-account-address-form hidden class="mt-8 border-t border-[#171511]/10 pt-8">
                                <h3 class="text-xl font-semibold tracking-[-0.03em]" data-account-address-form-title>Add address</h3>
                                <input type="hidden" name="addressId">
                                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                    <div class="flex flex-col gap-2">
                                        <label for="account-address-country" class="text-sm font-medium text-[#171511]">Country</label>
                                        <select id="account-address-country" name="country" autocomplete="country" class="min-h-12 border border-[#171511]/15 bg-white px-4 text-base focus:border-[#a4432e] focus:outline-none focus:ring-2 focus:ring-[#a4432e]/20"></select>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label for="account-address-postcode" class="text-sm font-medium text-[#171511]">Postcode</label>
                                        <input id="account-address-postcode" name="postcode" autocomplete="postal-code" class="min-h-12 border border-[#171511]/15 bg-white px-4 text-base focus:border-[#a4432e] focus:outline-none focus:ring-2 focus:ring-[#a4432e]/20">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <button type="button" data-account-address-postcode-button class="inline-flex min-h-11 items-center justify-center border border-[#171511]/15 px-4 text-sm font-semibold text-[#171511] transition hover:bg-[#f5f0e8] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                                            Find address
                                        </button>
                                        <div data-account-address-postcode-results class="mt-3 space-y-2"></div>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label for="account-address-name" class="text-sm font-medium text-[#171511]">Address name</label>
                                        <input id="account-address-name" name="name" autocomplete="name" class="min-h-12 border border-[#171511]/15 bg-white px-4 text-base focus:border-[#a4432e] focus:outline-none focus:ring-2 focus:ring-[#a4432e]/20">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label for="account-address-administrative-division" class="text-sm font-medium text-[#171511]">County / state</label>
                                        <input id="account-address-administrative-division" name="administrativeDivision" autocomplete="address-level1" class="min-h-12 border border-[#171511]/15 bg-white px-4 text-base focus:border-[#a4432e] focus:outline-none focus:ring-2 focus:ring-[#a4432e]/20">
                                    </div>
                                    <div class="flex flex-col gap-2 sm:col-span-2">
                                        <label for="account-address-line-1" class="text-sm font-medium text-[#171511]">Address line 1</label>
                                        <input id="account-address-line-1" name="line1" autocomplete="address-line1" class="min-h-12 border border-[#171511]/15 bg-white px-4 text-base focus:border-[#a4432e] focus:outline-none focus:ring-2 focus:ring-[#a4432e]/20">
                                    </div>
                                    @foreach ([2, 3, 4, 5] as $lineNumber)
                                        <div class="flex flex-col gap-2">
                                            <label for="account-address-line-{{ $lineNumber }}" class="text-sm font-medium text-[#171511]">Address line {{ $lineNumber }}</label>
                                            <input id="account-address-line-{{ $lineNumber }}" name="line{{ $lineNumber }}" autocomplete="address-line{{ min($lineNumber, 3) }}" class="min-h-12 border border-[#171511]/15 bg-white px-4 text-base focus:border-[#a4432e] focus:outline-none focus:ring-2 focus:ring-[#a4432e]/20">
                                        </div>
                                    @endforeach
                                    <div class="flex flex-col gap-2">
                                        <label for="account-address-town" class="text-sm font-medium text-[#171511]">Town / city</label>
                                        <input id="account-address-town" name="town" autocomplete="address-level2" class="min-h-12 border border-[#171511]/15 bg-white px-4 text-base focus:border-[#a4432e] focus:outline-none focus:ring-2 focus:ring-[#a4432e]/20">
                                    </div>
                                    <div class="grid gap-3 sm:col-span-2 sm:grid-cols-2">
                                        <label class="flex min-h-12 items-center gap-3 border border-[#171511]/10 bg-[#f5f0e8] px-4 text-sm font-medium text-[#171511]">
                                            <input name="isBilling" type="checkbox" value="1" class="size-4 accent-[#a4432e]">
                                            Use for billing
                                        </label>
                                        <label class="flex min-h-12 items-center gap-3 border border-[#171511]/10 bg-[#f5f0e8] px-4 text-sm font-medium text-[#171511]">
                                            <input name="isDelivery" type="checkbox" value="1" class="size-4 accent-[#a4432e]">
                                            Use for delivery
                                        </label>
                                    </div>
                                </div>
                                <p data-account-address-feedback tabindex="-1" role="status" hidden class="mt-5 border border-[#a4432e]/25 bg-[#f5f0e8] px-4 py-3 text-sm leading-6 text-[#5d5549]">Address saved.</p>
                                <p data-account-address-error tabindex="-1" role="alert" hidden class="mt-5 border border-[#a4432e]/25 bg-[#f5f0e8] px-4 py-3 text-sm leading-6 text-[#7b3021]">We could not save this address. Please check the details and try again.</p>
                                <div class="mt-5 flex flex-wrap gap-3">
                                    <button type="submit" data-account-address-submit class="inline-flex min-h-12 items-center justify-center bg-[#a4432e] px-6 text-sm font-semibold text-white transition hover:bg-[#873625] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e] disabled:cursor-wait disabled:opacity-70">Save address</button>
                                    <button type="button" data-account-address-cancel-button class="inline-flex min-h-12 items-center justify-center border border-[#171511]/15 px-6 text-sm font-semibold text-[#171511] transition hover:bg-[#f5f0e8] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">Cancel</button>
                                </div>
                            </form>
                        </section>
                    @elseif ($activeSection === 'orders')
                        <section class="border border-[#171511]/10 bg-white p-6 shadow-sm sm:p-8">
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-[#a4432e]">Orders</p>
                            <h2 class="mt-3 text-3xl font-semibold tracking-[-0.04em]">Recent orders</h2>
                            <p data-account-orders-status class="mt-5 text-sm leading-6 text-[#5d5549]">Loading order history.</p>
                            <div data-account-orders class="mt-6 space-y-4 text-[#5d5549]"></div>
                            <div class="mt-8 border-t border-[#171511]/10 pt-8">
                                <h3 class="text-xl font-semibold tracking-[-0.03em]">E-tickets</h3>
                                <p data-account-print-at-home-documents-status class="mt-3 text-sm leading-6 text-[#5d5549]">Loading e-tickets.</p>
                                <div data-account-print-at-home-documents class="mt-5 grid gap-4 sm:grid-cols-2"></div>
                            </div>
                        </section>
                    @elseif ($activeSection === 'payments')
                        <section class="border border-[#171511]/10 bg-white p-6 shadow-sm sm:p-8">
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-[#a4432e]">Payments</p>
                            <h2 class="mt-3 text-3xl font-semibold tracking-[-0.04em]">Stored cards</h2>
                            <p class="mt-4 leading-7 text-[#5d5549]">
                                Saved payment cards are managed securely by Spektrix. Cue only shows masked card details.
                            </p>
                            <label class="mt-5 flex min-h-12 items-center gap-3 border border-[#171511]/10 bg-[#f5f0e8] px-4 text-sm font-medium text-[#171511]">
                                <input data-account-stored-cards-include-pending type="checkbox" value="1" class="size-4 accent-[#a4432e]">
                                Include pending cards
                            </label>
                            <p data-account-stored-cards-status class="mt-5 text-sm leading-6 text-[#5d5549]">Loading stored cards.</p>
                            <div data-account-stored-cards class="mt-6 grid gap-4 text-[#5d5549] sm:grid-cols-2 lg:grid-cols-3"></div>
                        </section>
                    @elseif ($activeSection === 'security')
                        <section class="border border-[#171511]/10 bg-[#171511] p-6 text-[#fdf7ee] shadow-sm sm:p-8">
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-[#e5c8b8]">Security</p>
                            <h2 class="mt-3 text-3xl font-semibold tracking-[-0.04em]">Password</h2>
                            <p class="mt-4 leading-7 text-[#e5c8b8]">
                                Change your password here if you know your current password.
                            </p>
                            <form method="post" action="{{ $customerSession->changePasswordUrl }}" data-account-password-form class="mt-6 space-y-4">
                                <div class="flex flex-col gap-2">
                                    <label for="account-old-password" class="text-sm font-medium text-[#fdf7ee]">Current password</label>
                                    <input id="account-old-password" name="oldPassword" type="password" autocomplete="current-password" class="min-h-12 border border-[#fdf7ee]/20 bg-[#fdf7ee] px-4 text-base text-[#171511] focus:outline-none focus:ring-2 focus:ring-[#e5c8b8]">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="account-new-password" class="text-sm font-medium text-[#fdf7ee]">New password</label>
                                    <input id="account-new-password" name="newPassword" type="password" autocomplete="new-password" minlength="8" class="min-h-12 border border-[#fdf7ee]/20 bg-[#fdf7ee] px-4 text-base text-[#171511] focus:outline-none focus:ring-2 focus:ring-[#e5c8b8]">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="account-new-password-confirmation" class="text-sm font-medium text-[#fdf7ee]">Confirm new password</label>
                                    <input id="account-new-password-confirmation" name="newPassword_confirmation" type="password" autocomplete="new-password" minlength="8" class="min-h-12 border border-[#fdf7ee]/20 bg-[#fdf7ee] px-4 text-base text-[#171511] focus:outline-none focus:ring-2 focus:ring-[#e5c8b8]">
                                </div>
                                <p data-account-password-feedback tabindex="-1" role="status" hidden class="border border-[#fdf7ee]/20 bg-[#fdf7ee]/10 px-4 py-3 text-sm leading-6 text-[#fdf7ee]">
                                    Password updated.
                                </p>
                                <p data-account-password-error tabindex="-1" role="alert" hidden class="border border-[#fdf7ee]/20 bg-[#fdf7ee]/10 px-4 py-3 text-sm leading-6 text-[#fdf7ee]">
                                    We could not update your password. Please check your current password and try again.
                                </p>
                                <button type="submit" data-account-password-submit class="inline-flex min-h-12 items-center justify-center bg-[#fdf7ee] px-6 text-sm font-semibold text-[#171511] transition hover:bg-[#e5c8b8] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#fdf7ee] disabled:cursor-wait disabled:opacity-70">
                                    Change password
                                </button>
                            </form>

                            <div class="mt-8 border-t border-[#fdf7ee]/20 pt-8">
                                <h3 class="text-xl font-semibold tracking-[-0.03em]">Forgotten your current password?</h3>
                                <p class="mt-3 leading-7 text-[#e5c8b8]">
                                    Send yourself a reset link instead. To make sure that link can open the secure reset form, Cue will sign you out after requesting it.
                                </p>
                                <form
                                    method="post"
                                    action="{{ $customerSession->forgotPasswordUrl }}?domain={{ request()->getHost() }}"
                                    data-account-password-recovery-form
                                    data-domain="{{ request()->getHost() }}"
                                    class="mt-5 space-y-4"
                                >
                                    <div class="flex flex-col gap-2">
                                        <label for="account-password-recovery-email" class="text-sm font-medium text-[#fdf7ee]">Email address</label>
                                        <input id="account-password-recovery-email" name="emailAddress" type="email" autocomplete="email" class="min-h-12 border border-[#fdf7ee]/20 bg-[#fdf7ee] px-4 text-base text-[#171511] focus:outline-none focus:ring-2 focus:ring-[#e5c8b8]">
                                    </div>
                                    <p data-account-password-recovery-feedback tabindex="-1" role="status" hidden class="border border-[#fdf7ee]/20 bg-[#fdf7ee]/10 px-4 py-3 text-sm leading-6 text-[#fdf7ee]">
                                        If that email address has an account, reset instructions are on their way.
                                    </p>
                                    <p data-account-password-recovery-error tabindex="-1" role="alert" hidden class="border border-[#fdf7ee]/20 bg-[#fdf7ee]/10 px-4 py-3 text-sm leading-6 text-[#fdf7ee]">
                                        We could not send a reset link. Please check your email address and try again.
                                    </p>
                                    <div class="flex flex-wrap items-center gap-4">
                                        <button type="submit" data-account-password-recovery-submit class="inline-flex min-h-12 items-center justify-center bg-[#fdf7ee] px-6 text-sm font-semibold text-[#171511] transition hover:bg-[#e5c8b8] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#fdf7ee] disabled:cursor-wait disabled:opacity-70">
                                            Send password reset link
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </section>
                    @else
                        <section class="border border-[#171511]/10 bg-white p-6 shadow-sm sm:p-8">
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-[#a4432e]">Contact preferences</p>
                            <h2 class="mt-3 text-3xl font-semibold tracking-[-0.04em]">Communication choices</h2>
                            <p class="mt-4 leading-7 text-[#5d5549]">
                                Choose which Spektrix contact preferences should be agreed for your ticketing account.
                            </p>
                            <form method="post" action="{{ $customerSession->agreedStatementsUrl }}" data-account-contact-preferences-form class="mt-6">
                                <div data-account-contact-preferences class="space-y-4 text-[#5d5549]">
                                    Loading contact preferences.
                                </div>
                                <p data-account-contact-preferences-feedback tabindex="-1" role="status" hidden class="mt-5 border border-[#a4432e]/25 bg-[#f5f0e8] px-4 py-3 text-sm leading-6 text-[#5d5549]">
                                    Contact preferences updated.
                                </p>
                                <p data-account-contact-preferences-error tabindex="-1" role="alert" hidden class="mt-5 border border-[#a4432e]/25 bg-[#f5f0e8] px-4 py-3 text-sm leading-6 text-[#7b3021]">
                                    We could not update your contact preferences. Please try again.
                                </p>
                                <button type="submit" data-account-contact-preferences-submit class="mt-5 inline-flex min-h-12 items-center justify-center bg-[#a4432e] px-6 text-sm font-semibold text-white transition hover:bg-[#873625] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e] disabled:cursor-wait disabled:opacity-70">
                                    Save preferences
                                </button>
                            </form>
                        </section>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
