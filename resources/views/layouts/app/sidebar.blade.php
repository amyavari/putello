@php
    /** The household of the page, or null on pages without one (D26). */
    $household = request()->route('household');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-bg text-ink">
        <x-shell.skip-link />

        <flux:sidebar sticky collapsible="mobile" class="w-64 border-e border-border bg-bg-subtle">
            <flux:sidebar.header>
                <x-shell.brand :$household />
                <flux:sidebar.collapse class="lg:hidden" :aria-label="__('shell.close_menu')" />
            </flux:sidebar.header>

            <x-shell.menu :$household />

            <flux:spacer />

            <x-shell.account-menu />
        </flux:sidebar>

        <x-shell.mobile-header :$household />

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
