@props(['household'])

{{-- The sections of the household of the page, a divider, then all the households. --}}
<flux:sidebar.nav :aria-label="__('shell.main_menu')">
    @if ($household)
        <flux:sidebar.item
            icon="squares-2x2"
            :href="route('places.index', $household)"
            :current="request()->routeIs('places.*', 'items.*', 'archived.*')"
            wire:navigate
        >
            {{ __('places.title') }}
        </flux:sidebar.item>

        <flux:separator class="my-2" />
    @endif

    <flux:sidebar.item icon="home" :href="route('households.index')" :current="request()->routeIs('households.*')" wire:navigate>
        {{ __('households.title') }}
    </flux:sidebar.item>
</flux:sidebar.nav>
