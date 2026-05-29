@extends('layouts.public', [
    'metaTitle' => 'Log in',
    'metaDescription' => 'Log in securely to manage your ticketing account.',
    'canonicalUrl' => route('ticketing.login'),
])

@section('content')
    <section
        class="px-5 pb-14 pt-10 sm:px-8 lg:px-12 lg:pt-16"
        data-customer-login-page
        @if ($customerSession !== null)
            data-customer-url="{{ $customerSession->customerUrl }}"
            data-account-url="{{ route('ticketing.account') }}"
        @endif
    >
        <div class="mx-auto max-w-5xl">
            <a href="{{ route('events.index') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-[#a4432e] underline decoration-[#a4432e]/35 underline-offset-4 hover:decoration-[#a4432e] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                Back to events
            </a>

            <div class="mt-8 grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(22rem,28rem)] lg:items-start lg:gap-16">
                <header class="max-w-xl">
                    <p class="text-sm font-medium uppercase tracking-[0.24em] text-[#a4432e]">Your account</p>
                    <h1 class="mt-4 text-4xl font-semibold tracking-[-0.05em] sm:text-5xl">Log in</h1>
                    <p class="mt-5 text-base leading-7 text-[#5d5549]">
                        Log in securely to access your ticketing account and continue with your booking.
                    </p>
                    <p class="mt-8 text-sm leading-6 text-[#5d5549]">
                        Do not have an account?
                        <a href="{{ route('ticketing.register') }}" class="inline-flex min-h-11 items-center font-medium text-[#a4432e] underline decoration-[#a4432e]/35 underline-offset-4 hover:decoration-[#a4432e] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                            Create an account
                        </a>
                    </p>
                </header>

                <div class="border border-[#171511]/12 bg-white p-6 sm:p-8">
                    @if (request()->query('account_created') === 'true')
                        <p role="status" class="mb-6 border border-[#a4432e]/25 bg-[#f5f0e8] px-4 py-3 text-sm leading-6 text-[#5d5549]">
                            Your account has been created. Log in to continue.
                        </p>
                    @endif

                    @if (request()->query('password_reset') === 'complete')
                        <p role="status" class="mb-6 border border-[#a4432e]/25 bg-[#f5f0e8] px-4 py-3 text-sm leading-6 text-[#5d5549]">
                            Your password has been updated. You can now log in.
                        </p>
                    @endif

                    @if (request()->query('password_reset') === 'requested')
                        <p role="status" class="mb-6 border border-[#a4432e]/25 bg-[#f5f0e8] px-4 py-3 text-sm leading-6 text-[#5d5549]">
                            Your reset link is on its way. We have signed you out so the link can open the secure password reset form.
                        </p>
                    @endif

                    <form
                        action="{{ $authentication->authenticateUrl }}"
                        method="post"
                        data-customer-login-form
                        data-authenticate-url="{{ $authentication->authenticateUrl }}"
                        data-success-url="{{ route('events.index') }}"
                        class="flex flex-col gap-6"
                    >
                        <div class="flex flex-col gap-2">
                            <label for="customer-login-email" class="text-sm font-medium text-[#171511]">Email address</label>
                            <input
                                id="customer-login-email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                class="min-h-12 w-full border border-[#171511]/18 bg-white px-4 text-base text-[#171511] outline-none transition focus:border-[#a4432e] focus:ring-2 focus:ring-[#a4432e]/20"
                            >
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="customer-login-password" class="text-sm font-medium text-[#171511]">Password</label>
                            <input
                                id="customer-login-password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                required
                                class="min-h-12 w-full border border-[#171511]/18 bg-white px-4 text-base text-[#171511] outline-none transition focus:border-[#a4432e] focus:ring-2 focus:ring-[#a4432e]/20"
                            >
                        </div>

                        <p data-customer-login-error tabindex="-1" role="alert" hidden class="border border-[#a4432e]/25 bg-[#f5f0e8] px-4 py-3 text-sm leading-6 text-[#7b3021]">
                            We could not log you in. Check your email address and password and try again.
                        </p>

                        <p data-customer-login-status role="status" class="sr-only" aria-live="polite"></p>

                        <button
                            type="submit"
                            data-customer-login-submit
                            class="inline-flex min-h-12 items-center justify-center bg-[#a4432e] px-6 text-sm font-semibold text-white transition hover:bg-[#873625] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e] disabled:cursor-wait disabled:opacity-70"
                        >
                            Log in
                        </button>
                    </form>

                    <div class="my-8 flex items-center gap-4 text-xs font-medium uppercase tracking-[0.18em] text-[#766d60]">
                        <span class="h-px flex-1 bg-[#171511]/12"></span>
                        Or
                        <span class="h-px flex-1 bg-[#171511]/12"></span>
                    </div>

                    <form
                        action="{{ $authentication->sendMagicLinkUrl }}"
                        method="post"
                        data-customer-magic-link-request-form
                        data-send-magic-link-url="{{ $authentication->sendMagicLinkUrl }}"
                        data-link-url="{{ route('ticketing.magic-link') }}?token={token}"
                        class="flex flex-col gap-5"
                    >
                        <input type="hidden" name="linkUrl" value="{{ route('ticketing.magic-link') }}?token={token}">
                        <h2 class="text-xl font-semibold tracking-[-0.035em]">Forgotten your password?</h2>
                        <p class="text-sm leading-6 text-[#5d5549]">We can email you a secure one-time sign-in link, so you can access your account without resetting your password.</p>

                        <div class="flex flex-col gap-2">
                            <label for="magic-link-email" class="text-sm font-medium text-[#171511]">Email address</label>
                            <input
                                id="magic-link-email"
                                name="emailAddress"
                                type="email"
                                autocomplete="email"
                                required
                                class="min-h-12 w-full border border-[#171511]/18 bg-white px-4 text-base text-[#171511] outline-none transition focus:border-[#a4432e] focus:ring-2 focus:ring-[#a4432e]/20"
                            >
                        </div>

                        <p data-customer-magic-link-feedback tabindex="-1" role="status" hidden class="border border-[#a4432e]/25 bg-[#f5f0e8] px-4 py-3 text-sm leading-6 text-[#5d5549]">
                            If we found an account for that email address, a sign-in link is on its way.
                        </p>

                        <p data-customer-magic-link-error tabindex="-1" role="alert" hidden class="border border-[#a4432e]/25 bg-[#f5f0e8] px-4 py-3 text-sm leading-6 text-[#7b3021]">
                            We could not request a sign-in link just now. Please try again.
                        </p>

                        <button
                            type="submit"
                            data-customer-magic-link-submit
                            class="inline-flex min-h-12 items-center justify-center border border-[#a4432e] px-6 text-sm font-semibold text-[#a4432e] transition hover:bg-[#a4432e] hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e] disabled:cursor-wait disabled:opacity-70"
                        >
                            Email me a sign-in link
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
