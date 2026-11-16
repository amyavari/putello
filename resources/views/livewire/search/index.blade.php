<x-page>
    <div class="space-y-2">
        <div class="flex min-h-11 items-center justify-between gap-3">
            <label for="search" class="text-[0.9375rem]/5 font-medium">{{ __('search.label') }}</label>

            @can('edit', $household)
                <flux:button :href="route('items.create', $household)" wire:navigate variant="primary" icon="plus">
                    {{ __('items.add') }}
                </flux:button>
            @endcan
        </div>

        <flux:input
            id="search"
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            :placeholder="__('search.placeholder')"
            autocomplete="off"
            :loading="false"
            autofocus
            class:input="h-13! text-lg!"
        />
    </div>

    <x-list.card wire:loading.flex wire:target="search" role="status" :aria-label="__('search.loading')" class="mt-6 flex-col">
        @foreach (range(1, 3) as $row)
            <div class="flex min-h-14 flex-col gap-2 px-4 py-3.5">
                <span aria-hidden="true" class="block h-4 w-2/5 rounded-md bg-bg-subtle"></span>
                <span aria-hidden="true" class="block h-3 w-3/4 rounded-md bg-bg-subtle"></span>
            </div>
        @endforeach
    </x-list.card>

    <div wire:loading.remove wire:target="search">
        @if ($this->matchingItems->isNotEmpty())
            <section class="mt-6 space-y-2">
                <x-page.section-heading>{{ __('search.items') }}</x-page.section-heading>

                <x-list.card>
                    @foreach ($this->matchingItems as $item)
                        <x-list.link-row
                            :href="route('items.show', [$household, $item])"
                            :name="$item->name"
                            :detail="$this->tree->pathOf($item->place)"
                            wire:key="item-{{ $item->id }}"
                        >
                            @if ($item->currentLoan !== null)
                                <x-slot:below>
                                    <x-loans.badge :loan="$item->currentLoan" />
                                </x-slot:below>
                            @endif
                        </x-list.link-row>
                    @endforeach
                </x-list.card>
            </section>
        @endif

        @if ($this->matchingPlaces->isNotEmpty())
            <section class="mt-6 space-y-2">
                <x-page.section-heading>{{ __('search.places') }}</x-page.section-heading>

                <x-list.card>
                    @foreach ($this->matchingPlaces as $place)
                        <x-list.link-row
                            :href="route('places.show', [$household, $place])"
                            :name="$place->name"
                            :detail="$this->tree->pathOf($place)"
                            wire:key="place-{{ $place->id }}"
                        />
                    @endforeach
                </x-list.card>
            </section>
        @endif

        @if ($this->isNothingFound())
            <flux:card class="mt-6 py-12 text-center">
                <p class="font-semibold">{{ __('search.nothing_found', ['search' => $search]) }}</p>
                <p class="mt-1 text-sm text-ink-muted">{{ __('search.nothing_found_hint') }}</p>

                @can('edit', $household)
                    <x-button.secondary :href="route('items.create', [$household, 'name' => $search])" wire:navigate icon="plus" class="mt-4">
                        {{ __('search.add_named_item', ['name' => $search]) }}
                    </x-button.secondary>
                @endcan
            </flux:card>
        @endif
    </div>
</x-page>
