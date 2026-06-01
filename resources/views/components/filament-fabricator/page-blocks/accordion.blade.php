@props(['items' => []])
<section class="px-5 py-8 sm:px-8 lg:px-12">
    <div class="mx-auto max-w-4xl space-y-3">
        @foreach (($items ?? []) as $item)
            <details class="rounded border border-[#171511]/15 bg-white p-4">
                <summary class="cursor-pointer text-base font-semibold text-[#171511]">{{ data_get($item, 'title') }}</summary>
                <div class="prose prose-stone mt-3 max-w-none">{!! data_get($item, 'content') !!}</div>
            </details>
        @endforeach
    </div>
</section>
