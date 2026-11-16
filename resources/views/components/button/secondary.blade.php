{{-- The olive button for a second action next to the primary one ("Add place", "Restore"). --}}
<flux:button variant="filled" {{ $attributes->class('bg-secondary! text-on-secondary! hover:bg-secondary-hover!') }}>
    {{ $slot }}
</flux:button>
