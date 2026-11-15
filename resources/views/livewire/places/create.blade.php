<section class="w-full max-w-xl">
    <x-places.breadcrumbs :$household :household-name="$this->householdName" :places="$this->placesAbove" />

    <flux:heading size="xl" level="1" class="mt-2">{{ __('places.add') }}</flux:heading>

    <form wire:submit="add" class="mt-6 space-y-6">
        <flux:input wire:model="name" :label="__('places.name')" autocomplete="off" />

        <div>
            <flux:text>{{ __('places.inside') }}</flux:text>

            <div class="mt-1 flex items-center justify-between gap-3">
                <flux:heading>{{ $parent->name ?? $this->householdName }}</flux:heading>

                <flux:modal.trigger name="choose-place">
                    <flux:button size="sm" variant="ghost">{{ __('places.change_place') }}</flux:button>
                </flux:modal.trigger>
            </div>

            <flux:error name="placeId" />
        </div>

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary" icon="plus" class="flex-[7]">{{ __('places.add') }}</flux:button>

            <flux:button
                :href="$parent !== null ? route('places.show', [$household, $parent]) : route('places.index', $household)"
                wire:navigate
                variant="ghost"
                class="flex-[3]"
            >
                {{ __('places.cancel') }}
            </flux:button>
        </div>
    </form>

    <flux:modal name="choose-place" class="w-full md:w-[30rem]">
        <livewire:places.picker
            :$household
            :chosen-place-id="$parent?->id"
            :key="'picker-'.($parent->id ?? 'household')"
            lazy
        />
    </flux:modal>
</section>
