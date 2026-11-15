<section class="w-full max-w-xl">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('places.index', $household)" wire:navigate>
            {{ $this->householdName }}
        </flux:breadcrumbs.item>

        @if ($parent !== null)
            <flux:breadcrumbs.item>{{ $parent->name }}</flux:breadcrumbs.item>
        @endif
    </flux:breadcrumbs>

    <flux:heading size="xl" level="1" class="mt-2">{{ __('places.add') }}</flux:heading>

    <form wire:submit="add" class="mt-6 space-y-6">
        <flux:input wire:model="name" :label="__('places.name')" autocomplete="off" />

        <div>
            <flux:text>{{ __('places.inside') }}</flux:text>
            <flux:heading class="mt-1">{{ $parent->name ?? $this->householdName }}</flux:heading>
        </div>

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary" icon="plus" class="flex-[7]">{{ __('places.add') }}</flux:button>

            <flux:button :href="route('places.index', $household)" wire:navigate variant="ghost" class="flex-[3]">
                {{ __('places.cancel') }}
            </flux:button>
        </div>
    </form>
</section>
