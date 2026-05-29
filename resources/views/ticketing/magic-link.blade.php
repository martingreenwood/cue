@extends('layouts.public', [
    'metaTitle' => 'Complete sign in',
    'metaDescription' => 'Complete your secure ticketing account sign in.',
    'canonicalUrl' => route('ticketing.magic-link'),
])

@section('content')
    <section class="px-5 pb-14 pt-10 sm:px-8 lg:px-12 lg:pt-16">
        <div class="mx-auto max-w-xl">
            <a href="{{ route('ticketing.login') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-[#a4432e] underline decoration-[#a4432e]/35 underline-offset-4 hover:decoration-[#a4432e] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                Back to log in
            </a>

            <div class="mt-8 border border-[#171511]/12 bg-white p-6 sm:p-8">
                <p class="text-sm font-medium uppercase tracking-[0.24em] text-[#a4432e]">Your account</p>
                <h1 class="mt-4 text-3xl font-semibold tracking-[-0.05em]">Complete sign in</h1>
                <p class="mt-4 text-sm leading-6 text-[#5d5549]">Use your secure link to sign in to your ticketing account.</p>

                <form
                    action="{{ $authentication->authenticateMagicLinkUrl }}"
                    method="post"
                    data-customer-magic-link-authentication-form
                    data-authenticate-magic-link-url="{{ $authentication->authenticateMagicLinkUrl }}"
                    data-success-url="{{ route('events.index') }}"
                    class="mt-8 flex flex-col gap-5"
                >
                    @if (request()->filled('token'))
                        <input type="hidden" name="token" value="{{ request()->query('token') }}">
                    @endif

                    <p data-customer-magic-link-authentication-error tabindex="-1" role="alert" hidden class="border border-[#a4432e]/25 bg-[#f5f0e8] px-4 py-3 text-sm leading-6 text-[#7b3021]">
                        This sign-in link is invalid or has expired. Request a new sign-in link to continue.
                    </p>

                    <button type="submit" data-customer-magic-link-authentication-submit class="inline-flex min-h-12 items-center justify-center bg-[#a4432e] px-6 text-sm font-semibold text-white transition hover:bg-[#873625] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e] disabled:cursor-wait disabled:opacity-70">
                        Continue to sign in
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
