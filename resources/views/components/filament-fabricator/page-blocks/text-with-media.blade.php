@props([
    'title' => null,
    'subtitle' => null,
    'content' => null,
    'media_type' => 'image',
    'media_url' => null,
    'image_src' => null,
    'video_src' => null,
    'media_alt' => null,
    'layout' => 'media_left',
    'buttons' => [],
])

@php
    $layout = in_array($layout, ['media_left', 'text_left'], true) ? $layout : 'media_left';
    $mediaType = in_array($media_type, ['image', 'video'], true) ? $media_type : 'image';
    $imageSrc = $image_src ?? $media_url;
    $textContent = html_entity_decode((string) ($content ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $buttonClasses = [
        'primary' => 'bg-[#171511] text-white shadow-sm hover:bg-[#2a261f]',
        'secondary' => 'bg-white text-[#171511] ring-1 ring-inset ring-[#171511]/15 hover:bg-[#f8f3ea]',
        'link' => 'text-[#171511] underline decoration-[#171511]/25 underline-offset-4 hover:decoration-[#171511]',
    ];
    $mediaOrder = $layout === 'media_left' ? 'order-1 md:order-1' : 'order-1 md:order-2';
    $textOrder = $layout === 'media_left' ? 'order-2 md:order-2' : 'order-2 md:order-1';
@endphp

<section class="px-5 py-8 sm:px-8 lg:px-12">
    <div class="mx-auto grid max-w-5xl items-center gap-8 md:grid-cols-2">
        <div class="{{ $mediaOrder }}">
            @if ($mediaType === 'video' && filled($video_src ?? null))
                <video class="w-full rounded border border-[#171511]/10" controls playsinline>
                    <source src="{{ $video_src }}">
                </video>
            @elseif (filled($imageSrc ?? null))
                <img src="{{ $imageSrc }}" alt="{{ $media_alt ?? '' }}" class="w-full rounded border border-[#171511]/10" loading="lazy">
            @endif
        </div>

        <div class="{{ $textOrder }}">
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

            @if (filled($textContent))
                <div @class([
                    'prose prose-stone max-w-none',
                    'mt-6' => filled($title ?? null) || filled($subtitle ?? null),
                ])>
                    {!! $textContent !!}
                </div>
            @endif

            @if (filled($buttons ?? []))
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    @foreach ($buttons as $button)
                        <a
                            href="{{ data_get($button, 'url') }}"
                            target="{{ data_get($button, 'target', '_self') }}"
                            @if (data_get($button, 'target') === '_blank') rel="noopener noreferrer" @endif
                            @class([
                                'inline-flex items-center justify-center rounded-full px-5 py-3 text-sm font-semibold transition',
                                $buttonClasses[data_get($button, 'variant', 'primary')] ?? $buttonClasses['primary'],
                            ])
                        >
                            {{ data_get($button, 'text') }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
