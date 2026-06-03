@props([
    'title' => null,
    'subtitle' => null,
    'text' => null,
    'image_src' => null,
    'image_alt' => null,
    'video_src' => null,
    'video_autoplay' => true,
    'video_muted' => true,
    'video_loop' => true,
    'overlay_enabled' => true,
    'alignment' => 'left',
    'buttons' => [],
])

@php
    $hasMedia = filled($video_src ?? null) || filled($image_src ?? null);
    $alignment = in_array($alignment, ['left', 'center', 'right'], true) ? $alignment : 'left';
    $buttonClasses = [
        'primary' => 'bg-[#171511] text-white shadow-sm hover:bg-[#2a261f]',
        'secondary' => 'bg-white text-[#171511] ring-1 ring-inset ring-[#171511]/15 hover:bg-[#f8f3ea]',
        'link' => 'text-[#171511] underline decoration-[#171511]/25 underline-offset-4 hover:decoration-[#171511]',
    ];
    $alignmentClasses = [
        'left' => [
            'content' => 'mr-auto text-left',
            'buttons' => 'justify-start',
        ],
        'center' => [
            'content' => 'mx-auto text-center',
            'buttons' => 'justify-center',
        ],
        'right' => [
            'content' => 'ml-auto text-right',
            'buttons' => 'justify-end',
        ],
    ][$alignment];
@endphp

<section @class([
    'relative overflow-hidden px-5 sm:px-8 lg:px-12',
    'py-20 text-white' => $hasMedia,
    'py-12' => ! $hasMedia,
])>
    @if ($hasMedia)
        <div class="absolute inset-0 -z-10">
            @if (filled($video_src ?? null))
                <video
                    class="h-full w-full object-cover"
                    playsinline
                    @if ($video_autoplay) autoplay @endif
                    @if ($video_muted) muted @endif
                    @if ($video_loop) loop @endif
                >
                    <source src="{{ $video_src }}">
                </video>
            @elseif (filled($image_src ?? null))
                <img
                    src="{{ $image_src }}"
                    alt="{{ $image_alt ?? '' }}"
                    class="h-full w-full object-cover"
                    loading="eager"
                >
            @endif

            @if ($overlay_enabled)
                <div class="absolute inset-0 bg-[#171511]/55"></div>
            @endif
        </div>
    @endif

    <div class="mx-auto max-w-5xl">
        <div class="{{ $alignmentClasses['content'] }}">
            <h1 @class([
                'text-4xl font-semibold tracking-tight sm:text-5xl',
                'text-white' => $hasMedia,
                'text-[#171511]' => ! $hasMedia,
            ])>
                {{ $title ?? 'Hero title' }}
            </h1>

            @if (filled($subtitle ?? null))
                <p @class([
                    'mt-4 text-base leading-7 sm:text-lg',
                    'text-white/85' => $hasMedia,
                    'text-[#5d5549]' => ! $hasMedia,
                ])>
                    {{ $subtitle }}
                </p>
            @endif

            @if (filled($text ?? null))
                <div @class([
                    'prose mt-6 max-w-none',
                    'prose-invert' => $hasMedia,
                    'prose-stone' => ! $hasMedia,
                ])>
                    {!! $text !!}
                </div>
            @endif

            @if (filled($buttons ?? []))
                <div class="mt-8 flex flex-wrap items-center gap-3 {{ $alignmentClasses['buttons'] }}">
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
