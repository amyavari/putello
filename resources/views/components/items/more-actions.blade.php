@props(['household', 'item'])

{{-- The item's "More actions" menu: Edit, then Archive in red, which explains why it is off while the item is lent. --}}
<flux:dropdown position="bottom" align="start">
    <flux:button icon="ellipsis-horizontal" square :aria-label="__('items.more_actions')" />

    <flux:menu>
        <flux:menu.item :href="route('items.edit', [$household, $item])" wire:navigate icon="pencil-square">
            {{ __('items.edit') }}
        </flux:menu.item>

        <flux:menu.separator />

        @if ($item->isLent())
            <x-menu.danger-item icon="archive-box" disabled>{{ __('items.archive') }}</x-menu.danger-item>

            <p class="max-w-56 px-2 pb-1.5 text-sm text-ink-muted">{{ __('loans.close_loan_first') }}</p>
        @else
            <x-menu.danger-item wire:click="archive" icon="archive-box">
                {{ __('items.archive') }}
            </x-menu.danger-item>
        @endif
    </flux:menu>
</flux:dropdown>
