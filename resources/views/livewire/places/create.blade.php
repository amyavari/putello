<x-page>
    <x-places.breadcrumbs :$household :household-name="$this->householdName" :places="$this->placesAbove" />

    <x-page.title class="mt-1">{{ __('places.add') }}</x-page.title>

    <flux:card class="mt-6">
        <form wire:submit="add" class="space-y-4">
            <flux:input wire:model="name" :label="__('places.name')" autocomplete="off" />

            <x-form.place-field :label="__('places.inside')" :place-name="$parent->name ?? $this->householdName" />

            <x-form.actions>
                <flux:button type="submit" variant="primary" icon="plus">{{ __('places.add') }}</flux:button>

                <flux:button
                    :href="$parent !== null ? route('places.show', [$household, $parent]) : route('places.index', $household)"
                    wire:navigate
                    variant="ghost"
                >
                    {{ __('places.cancel') }}
                </flux:button>
            </x-form.actions>
        </form>
    </flux:card>

    <flux:modal name="choose-place" class="w-full md:w-[30rem]">
        <livewire:places.picker
            :$household
            :chosen-place-id="$parent?->id"
            :key="'picker-'.($parent->id ?? 'household')"
            lazy
        />
    </flux:modal>
</x-page>
