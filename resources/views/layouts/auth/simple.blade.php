<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-bg text-ink antialiased">
        <main class="mx-auto flex min-h-svh w-full max-w-[25rem] flex-col gap-6 px-4 py-12 md:py-24">
            <a href="{{ route('home') }}" wire:navigate class="self-start text-lg font-bold text-ink">{{ config('app.name') }}</a>

            {{ $slot }}
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
