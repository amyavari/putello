@props(['name', 'depth', 'hasChildren', 'href' => null])

<div
    class="flex min-h-11 items-center border-b border-zinc-200 pe-4 dark:border-zinc-700"
    style="padding-inline-start: {{ $depth * 1.5 }}rem"
>
    @if ($hasChildren)
        <flux:button
            variant="ghost"
            size="sm"
            square
            x-on:click="open = ! open"
            x-bind:aria-expanded="open"
            x-bind:aria-label="open ? {{ Js::from(__('places.collapse', ['name' => $name])) }} : {{ Js::from(__('places.expand', ['name' => $name])) }}"
        >
            <flux:icon.chevron-down variant="micro" x-show="open" />
            <flux:icon.chevron-right variant="micro" x-show="! open" class="rtl:rotate-180" />
        </flux:button>
    @else
        <span aria-hidden="true" class="size-8 shrink-0"></span>
    @endif

    @if ($slot->isNotEmpty())
        {{ $slot }}
    @elseif ($href !== null)
        <a href="{{ $href }}" wire:navigate class="min-w-0 flex-1 truncate hover:underline">{{ $name }}</a>
    @else
        <span class="min-w-0 flex-1 truncate">{{ $name }}</span>
    @endif
</div>
