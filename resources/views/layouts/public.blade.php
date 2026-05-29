<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#171511">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

        <title>{{ $metaTitle ?? 'What is on' }} | Cue</title>

        @isset($metaDescription)
            <meta name="description" content="{{ $metaDescription }}">
        @endisset

        @isset($canonicalUrl)
            <link rel="canonical" href="{{ $canonicalUrl }}">
        @endisset

        @isset($embedScriptUrl)
            <script src="{{ $embedScriptUrl }}" defer></script>
        @endisset

        @if (($customerSession ?? null) !== null)
            <script src="{{ $customerSession->componentLoaderUrl }}" async></script>
        @endif

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        @stack('head')
    </head>
    <body class="min-h-screen bg-[#f5f0e8] font-sans text-[#171511] antialiased">
        <a href="#content" class="sr-only z-50 rounded-full bg-[#a4432e] px-5 py-3 text-white focus:not-sr-only focus:fixed focus:left-5 focus:top-5">
            Skip to content
        </a>

        <x-ticketing.customer-session-bar :customer-session="$customerSession ?? null" :site-copy="$siteCopy" />

        <header class="border-b border-[#171511]/10 px-5 py-6 sm:px-8 lg:px-12">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6">
                <a href="{{ route('events.index') }}" class="inline-flex min-h-11 min-w-11 items-center text-2xl font-semibold tracking-[-0.05em] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                    Cue
                </a>
                <nav aria-label="Main navigation">
                    <a href="{{ route('events.index') }}" class="inline-flex min-h-11 items-center text-sm font-medium uppercase tracking-[0.18em] text-[#5d5549] hover:text-[#171511] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                        What is on
                    </a>
                </nav>
            </div>
        </header>

        <main id="content">
            @yield('content')
        </main>

        <footer class="mt-20 border-t border-[#171511]/10 px-5 py-8 text-sm text-[#5d5549] sm:px-8 lg:px-12">
            <div class="mx-auto max-w-7xl">{{ $siteCopy->footerAvailabilityNotice }}</div>
        </footer>
    </body>
</html>
