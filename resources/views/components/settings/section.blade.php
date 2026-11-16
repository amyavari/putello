@props(['heading', 'description' => null])

{{-- One Settings section: its heading, an optional line under it, then a card. --}}
<section>
    <x-page.section-heading>{{ $heading }}</x-page.section-heading>

    @if ($description !== null)
        <p class="text-sm text-ink-muted">{{ $description }}</p>
    @endif

    <flux:card {{ $attributes->class('mt-2') }}>
        {{ $slot }}
    </flux:card>
</section>
