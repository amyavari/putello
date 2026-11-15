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

    <div class="flex items-center justify-between gap-3">
        <flux:heading size="xl" level="1">{{ __('places.title') }}</flux:heading>

        @can('edit', $household)
            <flux:button :href="route('places.create', $household)" wire:navigate variant="primary" icon="plus">
                {{ __('places.add') }}
            </flux:button>
        @endcan
    </div>

    @if ($this->tree->roots()->isEmpty())
        <flux:card class="mt-6 text-center">
            <flux:heading>{{ __('places.empty.heading') }}</flux:heading>
            <flux:text class="mt-1">{{ __('places.empty.text') }}</flux:text>
        </flux:card>
    @else
        <flux:card class="mt-6 overflow-hidden p-0">
            <ul x-data="{ open: true }">
                <li>
                    <x-places.tree-row :name="$this->householdName" :depth="0" :has-children="true" />

                    <ul x-show="open">
                        @foreach ($this->tree->roots() as $place)
                            <x-places.tree-node :$household :$place :tree="$this->tree" :depth="1" wire:key="place-{{ $place->id }}" />
                        @endforeach
                    </ul>
                </li>
            </ul>
        </flux:card>
    @endif
</section>
