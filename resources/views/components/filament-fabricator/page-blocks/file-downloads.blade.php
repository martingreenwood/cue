@props([
    'title' => 'Downloads',
    'subtitle' => null,
    'downloads' => [],
])

@if (filled($downloads ?? []))
    <section class="px-5 py-8 sm:px-8 lg:px-12">
        <div class="mx-auto max-w-5xl">
            @if (filled($title ?? null) || filled($subtitle ?? null))
                <div class="max-w-3xl">
                    @if (filled($title ?? null))
                        <h2 class="text-3xl font-semibold tracking-tight text-[#171511] sm:text-4xl">
                            {{ $title }}
                        </h2>
                    @endif

                    @if (filled($subtitle ?? null))
                        <p class="mt-4 text-base leading-7 text-[#5d5549] sm:text-lg">
                            {{ $subtitle }}
                        </p>
                    @endif
                </div>
            @endif

            <div @class([
                'grid gap-4',
                'mt-8' => filled($title ?? null) || filled($subtitle ?? null),
            ])>
                @foreach ($downloads as $download)
                    <article class="rounded-2xl border border-[#171511]/10 bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl font-semibold text-[#171511]">
                                        {{ data_get($download, 'title') }}
                                    </h3>

                                    @if (filled(data_get($download, 'extension')))
                                        <span class="rounded-full bg-[#f8f3ea] px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-[#5d5549]">
                                            {{ data_get($download, 'extension') }}
                                        </span>
                                    @endif
                                </div>

                                @if (filled(data_get($download, 'description')))
                                    <p class="mt-2 text-sm leading-6 text-[#5d5549]">
                                        {{ data_get($download, 'description') }}
                                    </p>
                                @endif
                            </div>

                            <a
                                href="{{ data_get($download, 'url') }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-full bg-[#171511] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#2a261f]"
                            >
                                {{ data_get($download, 'button_text', 'View / download') }}
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
