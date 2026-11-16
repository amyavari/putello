<x-page>
    <x-flash-message :$message :undo="$archivedItemId !== null ? 'undoArchive' : null" />

    <x-places.breadcrumbs :$household :household-name="$this->householdName" :places="$this->tree->placesAbove($place)" />

    <x-page.title class="mt-1">
        {{ $place->name }}

        <x-slot:actions>
            @can('edit', $household)
                <x-places.more-actions :is-empty="$this->isEmpty" :not-empty-error-message="$this->notEmptyErrorMessage" />
            @endcan
        </x-slot:actions>
    </x-page.title>

    <x-page.section-heading class="mt-6">
        {{ __('places.items_in_this_place') }}

        <x-slot:actions>
            @can('edit', $household)
                <flux:button :href="route('items.create', [$household, 'inside' => $place])" wire:navigate variant="primary" icon="plus">
                    {{ __('items.add') }}
                </flux:button>
            @endcan
        </x-slot:actions>
    </x-page.section-heading>

    @if ($this->itemsInside->isEmpty())
        <x-list.empty class="mt-2">{{ __('places.nothing_here_yet') }}</x-list.empty>
    @else
        <x-list.card class="mt-2">
            @foreach ($this->itemsInside as $item)
                <x-list.link-row
                    :href="route('items.show', [$household, $item])"
                    :name="$item->name"
                    :detail="$this->tree->pathOf($place)"
                    wire:key="item-{{ $item->id }}"
                />
            @endforeach
        </x-list.card>
    @endif

    <x-page.section-heading class="mt-6">
        {{ __('places.places_inside') }}

        <x-slot:actions>
            @can('edit', $household)
                <x-button.secondary :href="route('places.create', [$household, 'inside' => $place])" wire:navigate icon="plus">
                    {{ __('places.add') }}
                </x-button.secondary>
            @endcan
        </x-slot:actions>
    </x-page.section-heading>

    @if (! $this->hasPlacesInside)
        <x-list.empty class="mt-2">{{ __('places.no_places_inside') }}</x-list.empty>
    @else
        <x-list.card class="mt-2">
            @foreach ($this->placesInside as $child)
                <x-list.link-row
                    :href="route('places.show', [$household, $child])"
                    :name="$child->name"
                    :detail="$this->tree->pathOf($child)"
                    wire:key="place-{{ $child->id }}"
                >
                    <x-places.number-of-items :count="$this->tree->numberOfItemsInside($child)" />
                </x-list.link-row>
            @endforeach
        </x-list.card>
    @endif

    @can('edit', $household)
        <flux:modal name="rename-place" class="w-full md:w-[30rem]">
            <livewire:places.rename :$household :$place :key="'rename-'.$place->id" />
        </flux:modal>

        <flux:modal name="move-place" class="w-full md:w-[30rem]">
            <div class="space-y-4">
                <livewire:places.picker :$household :$place :key="'picker-'.$place->name" lazy />

                <flux:error name="placeId" />
            </div>
        </flux:modal>

        <flux:modal name="delete-place" class="w-full md:w-[30rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg" class="font-semibold">{{ __('places.delete_heading', ['name' => $place->name]) }}</flux:heading>
                    <p class="mt-1 text-sm text-ink-muted">
                        {{ __('places.delete_description') }}
                        <x-cannot-be-undone />
                    </p>
                </div>

                <flux:error name="place" />

                <x-modal.actions>
                    <x-button.danger wire:click="delete">{{ __('places.delete_place') }}</x-button.danger>
                </x-modal.actions>
            </div>
        </flux:modal>
    @endcan
</x-page>
