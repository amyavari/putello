{{-- The fields Add item and Edit item share: Name and "Notes (optional)". --}}
<flux:input wire:model="name" :label="__('items.name')" autocomplete="off" />

<flux:textarea
    wire:model="notes"
    :label="__('items.notes_optional')"
    :description="__('items.notes_hint')"
    rows="3"
/>
