<form wire:submit="rename" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('places.rename_place') }}</flux:heading>
        <flux:text class="mt-1">{{ __('places.rename_description') }}</flux:text>
    </div>

    <flux:input wire:model="name" :label="__('places.name')" autocomplete="off" />

    <div class="flex gap-3">
        <flux:button type="submit" variant="primary">{{ __('places.rename') }}</flux:button>

        <flux:modal.close>
            <flux:button variant="ghost">{{ __('places.cancel') }}</flux:button>
        </flux:modal.close>
    </div>
</form>
