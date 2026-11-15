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

    <x-places.breadcrumbs :$household :household-name="$this->householdName" :places="$this->tree->ancestorsOf($place)" />

    <div class="mt-2 flex items-center justify-between gap-3">
        <flux:heading size="xl" level="1">{{ $place->name }}</flux:heading>

        @can('edit', $household)
            <flux:dropdown position="bottom" align="end">
                <flux:button icon="ellipsis-horizontal" square :aria-label="__('places.more_actions')" />

                <flux:menu>
                    <flux:modal.trigger name="rename-place">
                        <flux:menu.item icon="pencil-square">{{ __('places.rename') }}</flux:menu.item>
                    </flux:modal.trigger>
                </flux:menu>
            </flux:dropdown>
        @endcan
    </div>

    <div class="mt-6 flex items-center justify-between gap-3">
        <flux:heading size="lg" level="2">{{ __('places.places_inside') }}</flux:heading>

        @can('edit', $household)
            <flux:button :href="route('places.create', [$household, 'inside' => $place])" wire:navigate variant="filled" icon="plus">
                {{ __('places.add') }}
            </flux:button>
        @endcan
    </div>

    @php($children = $this->tree->childrenOf($place))

    @if ($children->isEmpty())
        <flux:card class="mt-2 text-center">
            <flux:text>{{ __('places.no_places_inside') }}</flux:text>
        </flux:card>
    @else
        <flux:card class="mt-2 divide-y divide-zinc-200 p-0 dark:divide-zinc-700">
            @foreach ($children as $child)
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
            <form wire:submit="rename" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('places.rename_place') }}</flux:heading>
                    <flux:text class="mt-1">{{ __('places.rename_description') }}</flux:text>
                </div>

                <flux:input wire:model="name" :label="__('places.name')" autocomplete="off" />

                <div class="flex gap-3">
                    <flux:button type="submit" variant="primary">{{ __('places.rename') }}</flux:button>

                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('places.cancel') }}</flux:button>
                    </flux:modal.close>
                </div>
            </form>
        </flux:modal>
    @endcan
</section>
