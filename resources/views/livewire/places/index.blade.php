<section class="w-full max-w-3xl">
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

    <flux:heading size="xl" level="1">{{ __('places.title') }}</flux:heading>

    <flux:card class="mt-6 text-center">
        <flux:heading>{{ __('places.empty.heading') }}</flux:heading>
        <flux:text class="mt-1">{{ __('places.empty.text') }}</flux:text>
    </flux:card>
</section>
