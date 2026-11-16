<x-page>
    <x-flash-message :$message />

    <x-page.title>
        {{ __('places.title') }}

        <x-slot:actions>
            @can('edit', $household)
                <flux:button :href="route('places.create', $household)" wire:navigate variant="primary" icon="plus">
                    {{ __('places.add') }}
                </flux:button>
            @endcan
        </x-slot:actions>
    </x-page.title>

    @if ($this->tree->topPlaces()->isEmpty())
        <flux:card class="mt-6 py-12 text-center">
            <p class="font-semibold">{{ __('places.empty.heading') }}</p>
            <p class="mt-1 text-sm text-ink-muted">{{ __('places.empty.text') }}</p>
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

    <a href="{{ route('archived.index', $household) }}" wire:navigate class="mt-6 inline-flex min-h-11 items-center gap-2 font-medium text-accent hover:text-accent-hover">
        <flux:icon.archive-box class="size-5" />
        {{ __('archived.link') }}
    </a>
</x-page>
