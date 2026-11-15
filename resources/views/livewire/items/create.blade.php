<section class="w-full max-w-xl">
    <x-places.breadcrumbs :$household :household-name="$this->householdName" :places="$this->path" />

    <flux:heading size="xl" level="1" class="mt-2">{{ __('items.add') }}</flux:heading>

    <form wire:submit="add" class="mt-6 space-y-6">
        <flux:input wire:model="name" :label="__('items.name')" autocomplete="off" />

        <flux:textarea
            wire:model="notes"
            :label="__('items.notes_optional')"
            :description="__('items.notes_hint')"
            rows="3"
        />

        <div>
            <flux:text>{{ __('items.place') }}</flux:text>

            <flux:heading class="mt-1">{{ $place->name }}</flux:heading>
        </div>

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary" icon="plus" class="flex-[7]">{{ __('items.add') }}</flux:button>

            <flux:button :href="route('places.show', [$household, $place])" wire:navigate variant="ghost" class="flex-[3]">
                {{ __('items.cancel') }}
            </flux:button>
        </div>
    </form>
</section>
