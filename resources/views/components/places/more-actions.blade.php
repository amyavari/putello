@props(['isEmpty', 'notEmptyErrorMessage'])

{{-- The place's "More actions" menu: Rename, Move, then Delete, which explains why it is off while the place is not empty. --}}
<flux:dropdown position="bottom" align="end">
    <flux:button icon="ellipsis-horizontal" square :aria-label="__('places.more_actions')" />

    <flux:menu>
        <flux:modal.trigger name="rename-place">
            <flux:menu.item icon="pencil-square">{{ __('places.rename') }}</flux:menu.item>
        </flux:modal.trigger>

        <flux:modal.trigger name="move-place">
            <flux:menu.item icon="folder-arrow-down">{{ __('places.move') }}</flux:menu.item>
        </flux:modal.trigger>

        <flux:menu.separator />

        @if ($isEmpty)
            <flux:modal.trigger name="delete-place">
                <x-menu.danger-item icon="trash">{{ __('places.delete') }}</x-menu.danger-item>
            </flux:modal.trigger>
        @else
            <x-menu.danger-item icon="trash" disabled>{{ __('places.delete') }}</x-menu.danger-item>

            <p class="max-w-56 px-2 pb-1.5 text-sm text-ink-muted">{{ $notEmptyErrorMessage }}</p>
        @endif
    </flux:menu>
</flux:dropdown>
