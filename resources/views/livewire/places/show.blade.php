<section class="w-full max-w-3xl">
    <x-flash-message :$message />

    <x-places.breadcrumbs :$household :household-name="$this->householdName" :places="$this->tree->placesAbove($place)" />

    <div class="mt-2 flex items-center justify-between gap-3">
        <flux:heading size="xl" level="1">{{ $place->name }}</flux:heading>

        @can('edit', $household)
            <flux:dropdown position="bottom" align="end">
                <flux:button icon="ellipsis-horizontal" square :aria-label="__('places.more_actions')" />

                <flux:menu>
                    <flux:modal.trigger name="rename-place">
                        <flux:menu.item icon="pencil-square">{{ __('places.rename') }}</flux:menu.item>
                    </flux:modal.trigger>

                    <flux:modal.trigger name="move-place">
                        <flux:menu.item icon="arrows-right-left">{{ __('places.move') }}</flux:menu.item>
                    </flux:modal.trigger>

                    <flux:menu.separator />

                    @if (! $this->hasPlacesInside)
                        <flux:modal.trigger name="delete-place">
                            <flux:menu.item icon="trash" variant="danger" class="text-red-600! dark:text-red-400! **:data-flux-menu-item-icon:text-current!">{{ __('places.delete') }}</flux:menu.item>
                        </flux:modal.trigger>
                    @else
                        <flux:menu.item icon="trash" variant="danger" class="text-red-600! dark:text-red-400! **:data-flux-menu-item-icon:text-current!" disabled>{{ __('places.delete') }}</flux:menu.item>

                        <flux:text size="sm" class="max-w-56 px-2 pb-1.5">
                            {{ $this->placesInsideErrorMessage }}
                        </flux:text>
                    @endif
                </flux:menu>
            </flux:dropdown>
        @endcan
    </div>

    <div class="mt-6 flex items-center justify-between gap-3">
        <flux:heading size="lg" level="2">{{ __('places.items_in_this_place') }}</flux:heading>

        @can('edit', $household)
            <flux:button :href="route('items.create', [$household, 'inside' => $place])" wire:navigate variant="primary" icon="plus">
                {{ __('items.add') }}
            </flux:button>
        @endcan
    </div>

    @if ($this->itemsInside->isEmpty())
        <flux:card class="mt-2 text-center">
            <flux:text>{{ __('places.nothing_here_yet') }}</flux:text>
        </flux:card>
    @else
        <flux:card class="mt-2 divide-y divide-zinc-200 p-0 dark:divide-zinc-700">
            @foreach ($this->itemsInside as $item)
                <a
                    href="{{ route('items.show', [$household, $item]) }}"
                    wire:key="item-{{ $item->id }}"
                    wire:navigate
                    class="flex items-center gap-3 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
                >
                    <div class="min-w-0 flex-1">
                        <flux:heading>{{ $item->name }}</flux:heading>
                        <flux:text>{{ $this->tree->pathOf($place) }}</flux:text>
                    </div>

                    <flux:icon.chevron-right variant="micro" class="text-zinc-400 rtl:rotate-180" />
                </a>
            @endforeach
        </flux:card>
    @endif

    <div class="mt-6 flex items-center justify-between gap-3">
        <flux:heading size="lg" level="2">{{ __('places.places_inside') }}</flux:heading>

        @can('edit', $household)
            <flux:button :href="route('places.create', [$household, 'inside' => $place])" wire:navigate variant="filled" icon="plus">
                {{ __('places.add') }}
            </flux:button>
        @endcan
    </div>

    @if (! $this->hasPlacesInside)
        <flux:card class="mt-2 text-center">
            <flux:text>{{ __('places.no_places_inside') }}</flux:text>
        </flux:card>
    @else
        <flux:card class="mt-2 divide-y divide-zinc-200 p-0 dark:divide-zinc-700">
            @foreach ($this->placesInside as $child)
                <a
                    href="{{ route('places.show', [$household, $child]) }}"
                    wire:key="place-{{ $child->id }}"
                    wire:navigate
                    class="flex items-center gap-3 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
                >
                    <div class="min-w-0 flex-1">
                        <flux:heading>{{ $child->name }}</flux:heading>
                        <flux:text>{{ $this->tree->pathOf($child) }}</flux:text>
                    </div>

                    <flux:icon.chevron-right variant="micro" class="text-zinc-400 rtl:rotate-180" />
                </a>
            @endforeach
        </flux:card>
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
                    <flux:heading size="lg">{{ __('places.delete_heading', ['name' => $place->name]) }}</flux:heading>
                    <flux:text class="mt-1">
                        {{ __('places.delete_description') }}
                        <strong>{{ __('places.cannot_be_undone') }}</strong>
                    </flux:text>
                </div>

                <flux:error name="place" />

                <div class="flex gap-3">
                    <flux:button wire:click="delete" variant="danger">{{ __('places.delete_place') }}</flux:button>

                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('places.cancel') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        </flux:modal>
    @endcan
</section>
