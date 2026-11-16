@props(['href', 'name', 'detail' => null])

{{-- A row that opens a page: the name, a detail under it (a path, a member count), anything in the slot, then a chevron. --}}
<a href="{{ $href }}" wire:navigate {{ $attributes->class('flex min-h-14 items-center gap-3 px-4 py-3 hover:bg-surface-hover') }}>
    <div class="min-w-0 flex-1">
        <p class="truncate font-semibold text-ink">{{ $name }}</p>

        @if ($detail !== null)
            <p class="truncate text-sm text-ink-muted">{{ $detail }}</p>
        @endif
    </div>

    {{ $slot }}

    <flux:icon.chevron-right class="size-5 shrink-0 text-ink-muted rtl:rotate-180" />
</a>
