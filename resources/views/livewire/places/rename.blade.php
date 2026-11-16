<form wire:submit="rename" class="space-y-6">
    <div>
        <flux:heading size="lg" class="font-semibold">{{ __('places.rename_place') }}</flux:heading>
        <p class="mt-1 text-sm text-ink-muted">{{ __('places.rename_description') }}</p>
    </div>

    <flux:input wire:model="name" :label="__('places.name')" autocomplete="off" />

    <x-modal.actions>
        <flux:button type="submit" variant="primary">{{ __('places.rename') }}</flux:button>
    </x-modal.actions>
</form>
