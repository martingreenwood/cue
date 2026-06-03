@props([
    'title' => null,
    'subtitle' => null,
    'left_content' => null,
    'right_content' => null,
    'alignment' => 'left',
    'buttons' => [],
])

@php
    $alignment = in_array($alignment, ['left', 'center', 'right'], true) ? $alignment : 'left';
    $leftContent = html_entity_decode((string) ($left_content ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $rightContent = html_entity_decode((string) ($right_content ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $buttonClasses = [
        'primary' => 'bg-[#171511] text-white shadow-sm hover:bg-[#2a261f]',
        'secondary' => 'bg-white text-[#171511] ring-1 ring-inset ring-[#171511]/15 hover:bg-[#f8f3ea]',
        'link' => 'text-[#171511] underline decoration-[#171511]/25 underline-offset-4 hover:decoration-[#171511]',
    ];
    $alignmentClasses = [
        'left' => [
            'text' => 'text-left',
            'buttons' => 'justify-start',
        ],
        'center' => [
            'text' => 'text-center',
            'buttons' => 'justify-center',
        ],
        'right' => [
            'text' => 'text-right',
            'buttons' => 'justify-end',
        ],
    ][$alignment];
@endphp

<section class="px-5 py-8 sm:px-8 lg:px-12">
    <div class="mx-auto max-w-5xl {{ $alignmentClasses['text'] }}">
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

        <div @class([
            'grid gap-6 md:grid-cols-2',
            'mt-6' => filled($title ?? null) || filled($subtitle ?? null),
        ])>
            <div class="prose prose-stone max-w-none">{!! $leftContent !!}</div>
            <div class="prose prose-stone max-w-none">{!! $rightContent !!}</div>
        </div>

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
</section>
