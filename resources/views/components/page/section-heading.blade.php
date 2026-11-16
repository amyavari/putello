@props(['actions' => null])

{{-- A section heading, with the section's button at the end of the same row. --}}
<div {{ $attributes->class('flex min-h-11 items-center justify-between gap-3') }}>
    <flux:heading level="2" class="text-lg/[1.625rem]! font-semibold!">{{ $slot }}</flux:heading>

    {{ $actions }}
</div>
