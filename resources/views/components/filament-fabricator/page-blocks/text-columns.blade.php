@props(['left_content' => null, 'right_content' => null])
<section class="px-5 py-8 sm:px-8 lg:px-12">
    <div class="mx-auto grid max-w-5xl gap-6 md:grid-cols-2">
        <div class="prose prose-stone max-w-none">{!! $left_content ?? '' !!}</div>
        <div class="prose prose-stone max-w-none">{!! $right_content ?? '' !!}</div>
    </div>
</section>
