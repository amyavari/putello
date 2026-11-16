<x-page>
    <x-places.breadcrumbs :$household :household-name="$this->householdName" :places="$this->path" :$item />

    <x-page.title class="mt-1">{{ __('items.edit_item') }}</x-page.title>

    <flux:card class="mt-6">
        <form wire:submit="save" class="space-y-4">
            <x-items.fields />

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('items.save') }}</flux:button>

                <flux:button :href="route('items.show', [$household, $item])" wire:navigate variant="ghost">
                    {{ __('items.cancel') }}
                </flux:button>
            </x-form.actions>
        </form>
    </flux:card>
</x-page>
