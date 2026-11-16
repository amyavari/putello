{{-- The red button that confirms a destructive action, as the last step of its modal. --}}
<flux:button variant="danger" {{ $attributes->class('bg-danger! text-on-danger! hover:bg-danger-hover!') }}>
    {{ $slot }}
</flux:button>
