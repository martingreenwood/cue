@extends('layouts.public', [
    'metaTitle' => 'Create an account',
    'metaDescription' => 'Create a ticketing account securely.',
    'canonicalUrl' => route('ticketing.register'),
])

@section('content')
    <section class="px-5 pb-14 pt-10 sm:px-8 lg:px-12 lg:pt-16">
        <div class="mx-auto max-w-2xl">
            <a href="{{ route('ticketing.login') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-[#a4432e] underline decoration-[#a4432e]/35 underline-offset-4 hover:decoration-[#a4432e] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                Back to log in
            </a>

            <header class="mt-8 max-w-xl">
                <p class="text-sm font-medium uppercase tracking-[0.24em] text-[#a4432e]">Your account</p>
                <h1 class="mt-4 text-4xl font-semibold tracking-[-0.05em] sm:text-5xl">Create an account</h1>
                <p class="mt-5 text-base leading-7 text-[#5d5549]">
                    Create an account to manage bookings and enjoy a quicker checkout experience.
                </p>
            </header>

            <form
                action="{{ $authentication->createCustomerUrl }}?domain={{ request()->getHost() }}"
                method="post"
                data-customer-registration-form
                data-create-customer-url="{{ $authentication->createCustomerUrl }}"
                data-domain="{{ request()->getHost() }}"
                data-success-url="{{ route('ticketing.login', ['account_created' => 'true']) }}"
                class="mt-10 grid gap-6 border border-[#171511]/12 bg-white p-6 sm:grid-cols-2 sm:p-8"
            >
                <div class="flex flex-col gap-2">
                    <label for="registration-first-name" class="text-sm font-medium text-[#171511]">First name</label>
                    <input id="registration-first-name" name="firstName" type="text" autocomplete="given-name" required class="min-h-12 w-full border border-[#171511]/18 bg-white px-4 text-base text-[#171511] outline-none transition focus:border-[#a4432e] focus:ring-2 focus:ring-[#a4432e]/20">
                </div>

                <div class="flex flex-col gap-2">
                    <label for="registration-last-name" class="text-sm font-medium text-[#171511]">Last name</label>
                    <input id="registration-last-name" name="lastName" type="text" autocomplete="family-name" required class="min-h-12 w-full border border-[#171511]/18 bg-white px-4 text-base text-[#171511] outline-none transition focus:border-[#a4432e] focus:ring-2 focus:ring-[#a4432e]/20">
                </div>

                <div class="flex flex-col gap-2 sm:col-span-2">
                    <label for="registration-email" class="text-sm font-medium text-[#171511]">Email address</label>
                    <input id="registration-email" name="email" type="email" autocomplete="email" required class="min-h-12 w-full border border-[#171511]/18 bg-white px-4 text-base text-[#171511] outline-none transition focus:border-[#a4432e] focus:ring-2 focus:ring-[#a4432e]/20">
                </div>

                <div class="flex flex-col gap-2">
                    <label for="registration-password" class="text-sm font-medium text-[#171511]">Password</label>
                    <input id="registration-password" name="password" type="password" autocomplete="new-password" minlength="8" required class="min-h-12 w-full border border-[#171511]/18 bg-white px-4 text-base text-[#171511] outline-none transition focus:border-[#a4432e] focus:ring-2 focus:ring-[#a4432e]/20">
                    <p class="text-sm text-[#5d5549]">At least 8 characters.</p>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="registration-password-confirmation" class="text-sm font-medium text-[#171511]">Confirm password</label>
                    <input id="registration-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required class="min-h-12 w-full border border-[#171511]/18 bg-white px-4 text-base text-[#171511] outline-none transition focus:border-[#a4432e] focus:ring-2 focus:ring-[#a4432e]/20">
                </div>

                <p data-customer-registration-error tabindex="-1" role="alert" hidden class="border border-[#a4432e]/25 bg-[#f5f0e8] px-4 py-3 text-sm leading-6 text-[#7b3021] sm:col-span-2"></p>

                <button
                    type="submit"
                    data-customer-registration-submit
                    class="inline-flex min-h-12 items-center justify-center bg-[#a4432e] px-6 text-sm font-semibold text-white transition hover:bg-[#873625] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e] disabled:cursor-wait disabled:opacity-70 sm:col-span-2"
                >
                    Create account
                </button>
            </form>
        </div>
    </section>
@endsection
