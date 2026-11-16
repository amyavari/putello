{{-- A destructive menu item (Delete, Archive, Log out): red text and icon. --}}
<flux:menu.item variant="danger" {{ $attributes->class('text-danger-ink! **:data-flux-menu-item-icon:text-current!') }}>
    {{ $slot }}
</flux:menu.item>
