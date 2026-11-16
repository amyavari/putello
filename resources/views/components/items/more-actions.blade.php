@props(['household', 'item'])

{{-- The item's "More actions" menu: Edit, then Archive in red. --}}
<flux:dropdown position="bottom" align="start">
    <flux:button icon="ellipsis-horizontal" square :aria-label="__('items.more_actions')" />

    <flux:menu>
        <flux:menu.item :href="route('items.edit', [$household, $item])" wire:navigate icon="pencil-square">
            {{ __('items.edit') }}
        </flux:menu.item>

        <flux:menu.separator />

        <x-menu.danger-item wire:click="archive" icon="archive-box">
            {{ __('items.archive') }}
        </x-menu.danger-item>
    </flux:menu>
</flux:dropdown>
