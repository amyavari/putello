@props(['name', 'isChosen', 'canBeChosen', 'path' => null])

<button
    type="button"
    {{ $attributes->class([
        'flex min-h-11 min-w-0 flex-1 items-center justify-between gap-3 text-start',
        'font-semibold text-accent-content' => $isChosen,
        'opacity-50' => ! $canBeChosen,
    ]) }}
    @disabled(! $canBeChosen)
    aria-pressed="{{ $isChosen ? 'true' : 'false' }}"
>
    <span class="min-w-0">
        <span class="block truncate">{{ $name }}</span>

        @if ($path !== null)
            <flux:text size="sm" class="truncate">{{ $path }}</flux:text>
        @endif
    </span>

    @if ($isChosen)
        <flux:icon.check variant="micro" />
    @endif
</button>
