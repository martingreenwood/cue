@extends('layouts.public', [
    'metaTitle' => $journey->title(),
    'metaDescription' => $journey->introduction(),
    'canonicalUrl' => route('ticketing.' . $journey->value),
    'embedScriptUrl' => $surface->embedScriptUrl,
])

@section('content')
    <section class="px-5 pb-14 pt-10 sm:px-8 lg:px-12 lg:pt-16">
        <div class="mx-auto max-w-5xl">
            <a href="{{ route($journey->backRouteName()) }}" class="inline-flex min-h-11 items-center text-sm font-medium text-[#a4432e] underline decoration-[#a4432e]/35 underline-offset-4 hover:decoration-[#a4432e] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]">
                {{ $journey->backLabel() }}
            </a>

            <header class="mt-8 max-w-2xl">
                <p class="text-sm font-medium uppercase tracking-[0.24em] text-[#a4432e]">Secure ticketing</p>
                <h1 class="mt-4 text-4xl font-semibold tracking-[-0.05em] sm:text-5xl">{{ $journey->title() }}</h1>
                <p class="mt-4 text-base leading-7 text-[#5d5549]">{{ $journey->introduction() }}</p>
            </header>

            <div class="mt-10 overflow-hidden border border-[#171511]/12 bg-white">
                <iframe
                    id="SpektrixIFrame"
                    name="SpektrixIFrame"
                    title="{{ $journey->title() }}"
                    src="{{ $surface->iframeUrl }}"
                    class="block h-[1000px] w-full bg-white"
                    height="1000"
                    loading="eager"
                ></iframe>
            </div>
        </div>
    </section>
@endsection
