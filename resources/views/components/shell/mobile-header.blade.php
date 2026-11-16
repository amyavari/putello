@props(['household'])

{{-- The header bar on small screens: the menu button, then "Putello – Home". --}}
<flux:header class="h-14 border-b border-border bg-bg-subtle lg:hidden">
    <flux:sidebar.toggle icon="bars-3" inset="left" :aria-label="__('shell.open_menu')" />

    <p class="ms-2 flex min-w-0 text-base" data-test="mobile-header-title">
        <span class="shrink-0 font-bold">{{ config('app.name') }}</span>

        @if ($household)
            <span class="truncate">&nbsp;– {{ $household->nameFor(auth()->user()) }}</span>
        @endif
    </p>
</flux:header>
