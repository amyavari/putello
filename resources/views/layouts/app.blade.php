<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main id="content" tabindex="-1" class="focus:outline-hidden max-lg:px-4!">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
