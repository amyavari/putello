@props(['name', 'isChosen', 'canBeChosen', 'path' => null])

<button
    type="button"
    {{ $attributes->class([
        'flex min-h-11 min-w-0 flex-1 items-center justify-between gap-3 text-start',
        'font-semibold text-on-accent-soft' => $isChosen,
        'opacity-50' => ! $canBeChosen,
    ]) }}
    @disabled(! $canBeChosen)
    aria-pressed="{{ $isChosen ? 'true' : 'false' }}"
>
    <span class="min-w-0">
        <span class="block truncate">{{ $name }}</span>

        @if ($path !== null)
            <span class="block truncate text-sm text-ink-muted">{{ $path }}</span>
        @endif
    </span>

    @if ($isChosen)
        <flux:icon.check class="size-5 shrink-0" />
    @endif
</button>
