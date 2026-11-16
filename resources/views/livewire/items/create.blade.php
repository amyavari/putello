<x-page>
    <x-places.breadcrumbs :$household :household-name="$this->householdName" :places="$this->path" />

    <x-page.title class="mt-1">{{ __('items.add') }}</x-page.title>

    <flux:card class="mt-6">
        <form wire:submit="add" class="space-y-4">
            <x-items.fields />

            <x-form.place-field :label="__('items.place')" :place-name="$place?->name" />

            <x-form.actions>
                <flux:button type="submit" variant="primary" icon="plus">{{ __('items.add') }}</flux:button>

                <flux:button :href="$place ? route('places.show', [$household, $place]) : route('search.index', $household)" wire:navigate variant="ghost">
                    {{ __('items.cancel') }}
                </flux:button>
            </x-form.actions>
        </form>
    </flux:card>

    <flux:modal name="choose-place" class="w-full md:w-[30rem]">
        <livewire:places.picker
            :$household
            :chosen-place-id="$place?->id"
            is-for-an-item
            :key="'picker-'.($place?->id ?? 'none')"
            lazy
        />
    </flux:modal>
</x-page>
