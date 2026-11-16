@props(['name', 'depth', 'hasChildren', 'href' => null, 'numberOfItems' => null])

<div
    class="flex min-h-11 items-center border-b border-border pe-4 has-[[aria-pressed=true]]:bg-accent-soft"
    style="padding-inline-start: {{ $depth * 1.5 }}rem"
>
    @if ($hasChildren)
        <flux:button
            variant="ghost"
            square
            class="shrink-0 text-ink-muted!"
            x-on:click="open = ! open"
            x-bind:aria-expanded="open"
            x-bind:aria-label="open ? {{ Js::from(__('places.collapse', ['name' => $name])) }} : {{ Js::from(__('places.expand', ['name' => $name])) }}"
        >
            <flux:icon.chevron-down class="size-5" x-show="open" />
            <flux:icon.chevron-right class="size-5 rtl:rotate-180" x-show="! open" />
        </flux:button>
    @else
        <span aria-hidden="true" class="size-11 shrink-0"></span>
    @endif

    @if ($slot->isNotEmpty())
        {{ $slot }}
    @elseif ($href !== null)
        <a href="{{ $href }}" wire:navigate class="flex min-h-11 min-w-0 flex-1 items-center truncate text-ink hover:underline">{{ $name }}</a>
    @else
        <span class="min-w-0 flex-1 truncate">{{ $name }}</span>
    @endif

    @if ($numberOfItems !== null)
        <x-places.number-of-items :count="$numberOfItems" />
    @endif
</div>
