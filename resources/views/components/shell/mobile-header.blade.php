@props(['household'])

{{-- The header bar on small screens: the menu button, "Putello – Home", then the search button (not on Search). --}}
<flux:header class="h-14 border-b border-border bg-bg-subtle lg:hidden">
    <flux:sidebar.toggle icon="bars-3" inset="left" :aria-label="__('shell.open_menu')" />

    <p class="ms-2 flex min-w-0 text-base" data-test="mobile-header-title">
        <span class="shrink-0 font-bold">{{ config('app.name') }}</span>

        @if ($household)
            <span class="truncate">&nbsp;– {{ $household->nameFor(auth()->user()) }}</span>
        @endif
    </p>

    @if ($household && ! request()->routeIs('search.*'))
        <flux:spacer />

        <flux:button
            variant="ghost"
            icon="magnifying-glass"
            :href="route('search.index', $household)"
            :aria-label="__('search.title')"
            wire:navigate
        />
    @endif
</flux:header>
