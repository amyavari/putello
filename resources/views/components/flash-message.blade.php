@props(['message'])

@if ($message !== null)
    <div
        wire:key="message-{{ md5($message) }}"
        x-data="{ visible: true }"
        x-init="setTimeout(() => visible = false, 5000)"
        x-show="visible"
        class="mb-6"
    >
        <flux:callout variant="success" icon="check-circle" :heading="$message" role="status">
            <x-slot name="controls">
                <flux:button variant="ghost" size="sm" x-on:click="visible = false">
                    {{ __('places.dismiss') }}
                </flux:button>
            </x-slot>
        </flux:callout>
    </div>
@endif
