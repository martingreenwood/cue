@props(['page'])

<x-public-shell :meta-title="$page->title" :canonical-url="url($page->getUrl())">
    <x-filament-fabricator::page-blocks :blocks="$page->blocks" />
</x-public-shell>
