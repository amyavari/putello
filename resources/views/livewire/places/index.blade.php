<section class="w-full max-w-3xl">
    <x-flash-message :$message />

    <div class="flex items-center justify-between gap-3">
        <flux:heading size="xl" level="1">{{ __('places.title') }}</flux:heading>

        @can('edit', $household)
            <flux:button :href="route('places.create', $household)" wire:navigate variant="primary" icon="plus">
                {{ __('places.add') }}
            </flux:button>
        @endcan
    </div>

    @if ($this->tree->topPlaces()->isEmpty())
        <flux:card class="mt-6 text-center">
            <flux:heading>{{ __('places.empty.heading') }}</flux:heading>
            <flux:text class="mt-1">{{ __('places.empty.text') }}</flux:text>
        </flux:card>
    @else
        <flux:card class="mt-6 overflow-hidden p-0">
            <ul x-data="{ open: true }">
                <li>
                    <x-places.tree-row :name="$this->householdName" :depth="0" :has-children="true" :number-of-items="$this->tree->numberOfAllItems()" />

                    <ul x-show="open">
                        @foreach ($this->tree->topPlaces() as $place)
                            <x-places.tree-node :$household :$place :tree="$this->tree" :depth="1" wire:key="place-{{ $place->id }}" />
                        @endforeach
                    </ul>
                </li>
            </ul>
        </flux:card>
    @endif

    <flux:link :href="route('archived.index', $household)" wire:navigate class="mt-6 inline-block">
        {{ __('archived.link') }}
    </flux:link>
</section>
