@props(['href'])

{{-- One crumb: a link with a 44px tap height, and "›" before every crumb but the first. --}}
<span {{ $attributes->class('flex items-center gap-x-2 [&:first-child>.separator]:hidden') }}>
    <span aria-hidden="true" class="separator text-ink-muted">›</span>
    <a href="{{ $href }}" wire:navigate class="inline-flex min-h-11 items-center font-medium text-accent underline underline-offset-2 hover:text-accent-hover">{{ $slot }}</a>
</span>
