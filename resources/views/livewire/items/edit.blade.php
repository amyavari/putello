<section class="w-full max-w-xl">
    <x-places.breadcrumbs :$household :household-name="$this->householdName" :places="$this->path" :$item />

    <flux:heading size="xl" level="1" class="mt-2">{{ __('items.edit_item') }}</flux:heading>

    <form wire:submit="save" class="mt-6 space-y-6">
        <flux:input wire:model="name" :label="__('items.name')" autocomplete="off" />

        <flux:textarea
            wire:model="notes"
            :label="__('items.notes_optional')"
            :description="__('items.notes_hint')"
            rows="3"
        />

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary" class="flex-[7]">{{ __('items.save') }}</flux:button>

            <flux:button :href="route('items.show', [$household, $item])" wire:navigate variant="ghost" class="flex-[3]">
                {{ __('items.cancel') }}
            </flux:button>
        </div>
    </form>
</section>
