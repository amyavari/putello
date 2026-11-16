@props(['actions' => null])

{{-- The page title, with the page's buttons at the end of the same row. --}}
<div {{ $attributes->class('flex min-h-11 items-center justify-between gap-3') }}>
    <flux:heading level="1" class="min-w-0 text-[1.375rem]/7! font-semibold!">{{ $slot }}</flux:heading>

    {{ $actions }}
</div>
