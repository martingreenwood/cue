@php
    $icons = [
        'book-open' => '<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>',
        'contrast' => '<circle cx="12" cy="12" r="10"/><path d="M12 18a6 6 0 0 0 0-12z"/>',
        'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'focus' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v4"/><path d="M12 18v4"/><path d="M2 12h4"/><path d="M18 12h4"/>',
        'image-off' => '<line x1="2" x2="22" y1="2" y2="22"/><path d="M10.41 10.41 8 13l-2-2-3 3"/><path d="M13.5 6.5 15 5l5 5"/><path d="M3.59 3.59A2 2 0 0 0 3 5v14a2 2 0 0 0 2 2h14a2 2 0 0 0 1.41-.59"/><path d="M21 15V5a2 2 0 0 0-2-2H9"/>',
        'link' => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
        'maximize' => '<path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/>',
        'moon' => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
        'mouse-pointer-2' => '<path d="m4 4 7.07 17 2.51-7.39L21 11.07z"/>',
        'move-horizontal' => '<path d="m18 8 4 4-4 4"/><path d="m6 8-4 4 4 4"/><path d="M2 12h20"/>',
        'palette' => '<circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 22a10 10 0 1 1 10-10 4 4 0 0 1-4 4h-1.5a2.5 2.5 0 0 0 0 5H12Z"/>',
        'pilcrow' => '<path d="M13 4v16"/><path d="M17 4v16"/><path d="M19 4H9.5a4.5 4.5 0 0 0 0 9H13"/>',
        'scan-line' => '<path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M7 12h10"/>',
        'scan-text' => '<path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M7 8h8"/><path d="M7 12h10"/><path d="M7 16h6"/>',
        'sparkles' => '<path d="M9.94 15.5 8.5 14.06 7.06 15.5 8.5 16.94z"/><path d="M14.5 3 16 8l5 1.5-5 1.5-1.5 5-1.5-5L8 9.5 13 8z"/><path d="M4 17.5 5 20l2.5 1L5 22 4 24l-1-2-2.5-1L3 20z"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>',
        'text-cursor-input' => '<path d="M5 4h9"/><path d="M9 4v16"/><path d="M5 20h9"/><path d="M19 7v10"/><path d="M17 15l2 2 2-2"/><path d="M17 9l2-2 2 2"/>',
        'text-search' => '<path d="M21 6H3"/><path d="M10 12H3"/><path d="M10 18H3"/><circle cx="17" cy="15" r="3"/><path d="m21 19-2.1-2.1"/>',
        'type' => '<path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/>',
        'volume-x' => '<path d="M11 5 6 9H2v6h4l5 4z"/><path d="m22 9-6 6"/><path d="m16 9 6 6"/>',
        'wand' => '<path d="M15 4V2"/><path d="M15 16v-2"/><path d="M8 9h2"/><path d="M20 9h2"/><path d="m17.8 11.8 1.4 1.4"/><path d="m13.2 4.8-1.4-1.4"/><path d="m17.8 6.2 1.4-1.4"/><path d="m13.2 13.2-1.4 1.4"/><path d="M14 9a1 1 0 1 1 2 0 1 1 0 0 1-2 0"/><path d="m3 21 9-9"/>',
    ];

    $contentToggles = [
        ['setting' => 'readableFont', 'label' => 'Readable font', 'icon' => 'pilcrow'],
        ['setting' => 'highlightTitles', 'label' => 'Highlight titles', 'icon' => 'type'],
        ['setting' => 'highlightLinks', 'label' => 'Highlight links', 'icon' => 'link'],
        ['setting' => 'textMagnifier', 'label' => 'Text magnifier', 'icon' => 'text-search'],
    ];

    $colourOptions = [
        ['value' => 'default', 'label' => 'Default', 'icon' => 'palette'],
        ['value' => 'dark', 'label' => 'Dark contrast', 'icon' => 'moon'],
        ['value' => 'light', 'label' => 'Light contrast', 'icon' => 'sun'],
        ['value' => 'high', 'label' => 'High contrast', 'icon' => 'contrast'],
        ['value' => 'highSaturation', 'label' => 'High saturation', 'icon' => 'sparkles'],
        ['value' => 'monochrome', 'label' => 'Monochrome', 'icon' => 'eye'],
        ['value' => 'lowSaturation', 'label' => 'Low saturation', 'icon' => 'wand'],
    ];

    $orientationToggles = [
        ['setting' => 'mute', 'label' => 'Mute', 'icon' => 'volume-x'],
        ['setting' => 'hideImages', 'label' => 'Hide images', 'icon' => 'image-off'],
        ['setting' => 'readMode', 'label' => 'Read mode', 'icon' => 'book-open'],
        ['setting' => 'readingGuide', 'label' => 'Reading guide', 'icon' => 'scan-line'],
        ['setting' => 'stopAnimations', 'label' => 'Stop animations', 'icon' => 'focus'],
        ['setting' => 'readingMask', 'label' => 'Reading mask', 'icon' => 'scan-text'],
        ['setting' => 'highlightHover', 'label' => 'Highlight hover', 'icon' => 'mouse-pointer-2'],
        ['setting' => 'highlightFocus', 'label' => 'Highlight focus', 'icon' => 'focus'],
        ['setting' => 'bigBlackCursor', 'label' => 'Big black cursor', 'icon' => 'mouse-pointer-2'],
        ['setting' => 'bigWhiteCursor', 'label' => 'Big white cursor', 'icon' => 'mouse-pointer-2'],
    ];
@endphp

<aside
    class="fixed bottom-5 right-5 z-40 max-w-[calc(100vw-2.5rem)]"
    data-accessibility-toolbar
>
    <button
        type="button"
        class="inline-flex min-h-12 items-center gap-3 rounded-full border-2 border-[#171511] bg-[#fdf7ee] px-5 py-3 text-sm font-bold text-[#171511] shadow-[0_10px_0_#171511] transition hover:-translate-y-0.5 hover:shadow-[0_12px_0_#171511] focus-visible:outline-4 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e] motion-reduce:transition-none motion-reduce:hover:translate-y-0"
        data-accessibility-toggle
        aria-controls="accessibility-panel"
        aria-expanded="false"
    >
        <span aria-hidden="true">Aa</span>
        Accessibility
    </button>

    <section
        id="accessibility-panel"
        class="mt-4 hidden max-h-[min(42rem,calc(100vh-8rem))] w-[min(28rem,calc(100vw-2.5rem))] overflow-y-auto rounded-3xl border-2 border-[#171511] bg-[#fdf7ee] p-5 text-[#171511] shadow-[0_18px_0_#171511]"
        data-accessibility-panel
        aria-labelledby="accessibility-panel-title"
    >
        <div class="flex items-start justify-between gap-4">
            <div>
                <p id="accessibility-panel-title" class="text-lg font-black tracking-[-0.03em]">Accessibility tools</p>
                <p class="mt-1 text-sm leading-6 text-[#5d5549]">Tune Cue for reading, focus, motion, colour, and keyboard comfort.</p>
            </div>

            <button
                type="button"
                class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full border border-[#171511]/20 text-xl font-bold hover:bg-[#171511] hover:text-[#fdf7ee] focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-[#a4432e]"
                data-accessibility-close
                aria-label="Close accessibility tools"
            >
                ×
            </button>
        </div>

        <div class="mt-5 grid gap-5">
            <fieldset class="grid gap-3 rounded-2xl border border-[#171511]/15 p-4">
                <legend class="px-2 text-sm font-bold uppercase tracking-[0.16em] text-[#5d5549]">Content adjustments</legend>

                @foreach ([
                    ['label' => 'Content scaling', 'setting' => 'scale', 'min' => '1', 'max' => '1.3', 'step' => '0.05', 'value' => '1', 'percentBase' => '1'],
                    ['label' => 'Font size', 'setting' => 'fontSize', 'min' => '1', 'max' => '1.35', 'step' => '0.05', 'value' => '1', 'percentBase' => '1'],
                    ['label' => 'Line height', 'setting' => 'lineHeight', 'min' => '1.4', 'max' => '2.1', 'step' => '0.1', 'value' => '1.6', 'percentBase' => '1.6'],
                    ['label' => 'Letter spacing', 'setting' => 'letterSpacing', 'min' => '0', 'max' => '0.12', 'step' => '0.02', 'value' => '0', 'percentBase' => '0'],
                ] as $control)
                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-[#171511]/15 bg-white/45 p-3">
                        <span class="text-sm font-semibold">{{ $control['label'] }}</span>

                        <div class="inline-flex items-center overflow-hidden rounded-full border border-[#171511]/20 bg-[#fdf7ee]" data-accessibility-stepper>
                            <button
                                type="button"
                                class="inline-flex min-h-11 min-w-11 items-center justify-center border-r border-[#171511]/20 text-xl font-black hover:bg-[#171511] hover:text-[#fdf7ee] focus-visible:outline-4 focus-visible:outline-offset-[-4px] focus-visible:outline-[#a4432e]"
                                data-accessibility-step="decrease"
                                data-accessibility-step-target="{{ $control['setting'] }}"
                                aria-label="Decrease {{ strtolower($control['label']) }}"
                            >
                                −
                            </button>

                            <output
                                class="min-w-16 px-3 text-center text-sm font-bold tabular-nums"
                                data-accessibility-output="{{ $control['setting'] }}"
                                aria-live="polite"
                            >
                                0%
                            </output>

                            <button
                                type="button"
                                class="inline-flex min-h-11 min-w-11 items-center justify-center border-l border-[#171511]/20 text-xl font-black hover:bg-[#171511] hover:text-[#fdf7ee] focus-visible:outline-4 focus-visible:outline-offset-[-4px] focus-visible:outline-[#a4432e]"
                                data-accessibility-step="increase"
                                data-accessibility-step-target="{{ $control['setting'] }}"
                                aria-label="Increase {{ strtolower($control['label']) }}"
                            >
                                +
                            </button>
                        </div>

                        <input
                            type="hidden"
                            value="{{ $control['value'] }}"
                            min="{{ $control['min'] }}"
                            max="{{ $control['max'] }}"
                            step="{{ $control['step'] }}"
                            data-accessibility-percent-base="{{ $control['percentBase'] }}"
                            data-accessibility-setting="{{ $control['setting'] }}"
                        >
                    </div>
                @endforeach

                <label class="grid gap-2 text-sm font-semibold">
                    Text alignment
                    <select class="min-h-11 rounded-xl border border-[#171511]/20 bg-white px-3 text-[#171511]" data-accessibility-setting="textAlign">
                        <option value="initial">Default</option>
                        <option value="left">Left</option>
                        <option value="center">Centre</option>
                        <option value="right">Right</option>
                    </select>
                </label>

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach ($contentToggles as $toggle)
                        <label class="group block cursor-pointer">
                            <input class="peer sr-only" type="checkbox" data-accessibility-setting="{{ $toggle['setting'] }}">
                            <span class="grid min-h-28 place-items-center gap-3 rounded-2xl border border-[#171511]/15 bg-white/45 p-4 text-center text-sm font-bold transition hover:-translate-y-0.5 hover:border-[#a4432e] hover:bg-[#f7d488]/35 peer-checked:border-[#a4432e] peer-checked:bg-[#a4432e] peer-checked:text-[#fdf7ee] peer-focus-visible:outline-4 peer-focus-visible:outline-offset-4 peer-focus-visible:outline-[#a4432e] motion-reduce:transition-none motion-reduce:hover:translate-y-0" data-accessibility-option>
                                <svg class="h-7 w-7" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round">
                                    {!! $icons[$toggle['icon']] !!}
                                </svg>
                                <span>{{ $toggle['label'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <fieldset class="grid gap-3 rounded-2xl border border-[#171511]/15 p-4">
                <legend class="px-2 text-sm font-bold uppercase tracking-[0.16em] text-[#5d5549]">Colour adjustments</legend>

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach ($colourOptions as $option)
                        <label class="group block cursor-pointer">
                            <input class="peer sr-only" type="radio" name="accessibility-colour" value="{{ $option['value'] }}" data-accessibility-setting="colourMode" @checked($option['value'] === 'default')>
                            <span class="grid min-h-28 place-items-center gap-3 rounded-2xl border border-[#171511]/15 bg-white/45 p-4 text-center text-sm font-bold transition hover:-translate-y-0.5 hover:border-[#a4432e] hover:bg-[#f7d488]/35 peer-checked:border-[#a4432e] peer-checked:bg-[#a4432e] peer-checked:text-[#fdf7ee] peer-focus-visible:outline-4 peer-focus-visible:outline-offset-4 peer-focus-visible:outline-[#a4432e] motion-reduce:transition-none motion-reduce:hover:translate-y-0" data-accessibility-option>
                                <svg class="h-7 w-7" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round">
                                    {!! $icons[$option['icon']] !!}
                                </svg>
                                <span>{{ $option['label'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <fieldset class="grid gap-3 rounded-2xl border border-[#171511]/15 p-4">
                <legend class="px-2 text-sm font-bold uppercase tracking-[0.16em] text-[#5d5549]">Orientation adjustments</legend>

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach ($orientationToggles as $toggle)
                        <label class="group block cursor-pointer">
                            <input class="peer sr-only" type="checkbox" data-accessibility-setting="{{ $toggle['setting'] }}">
                            <span class="grid min-h-28 place-items-center gap-3 rounded-2xl border border-[#171511]/15 bg-white/45 p-4 text-center text-sm font-bold transition hover:-translate-y-0.5 hover:border-[#a4432e] hover:bg-[#f7d488]/35 peer-checked:border-[#a4432e] peer-checked:bg-[#a4432e] peer-checked:text-[#fdf7ee] peer-focus-visible:outline-4 peer-focus-visible:outline-offset-4 peer-focus-visible:outline-[#a4432e] motion-reduce:transition-none motion-reduce:hover:translate-y-0" data-accessibility-option>
                                <svg class="h-7 w-7" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round">
                                    {!! $icons[$toggle['icon']] !!}
                                </svg>
                                <span>{{ $toggle['label'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <button
                type="button"
                class="inline-flex min-h-12 items-center justify-center rounded-full bg-[#171511] px-5 py-3 text-sm font-bold text-[#fdf7ee] hover:bg-[#a4432e] focus-visible:outline-4 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]"
                data-accessibility-reset
            >
                Reset accessibility settings
            </button>
        </div>
    </section>

    <div class="pointer-events-none fixed left-0 right-0 top-1/2 z-30 hidden h-12 -translate-y-1/2 border-y-2 border-[#a4432e] bg-[#f7d488]/25" data-accessibility-guide></div>
    <div class="pointer-events-none fixed inset-0 z-30 hidden" data-accessibility-mask></div>
    <div class="pointer-events-none fixed left-4 top-4 z-50 hidden max-w-sm rounded-2xl border-2 border-[#171511] bg-[#fdf7ee] px-4 py-3 text-xl font-bold leading-relaxed text-[#171511] shadow-[0_8px_0_#171511]" data-accessibility-magnifier></div>
</aside>
