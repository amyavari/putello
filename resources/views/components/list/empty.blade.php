{{-- An empty list inside a page: one quiet line in a card. --}}
<flux:card {{ $attributes->class('py-8 text-center') }}>
    <p class="text-ink-muted">{{ $slot }}</p>
</flux:card>
