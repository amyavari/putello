<section class="w-full max-w-3xl">
    <x-flash-message :$message />

    <x-places.breadcrumbs :$household :household-name="$this->householdName" :places="$this->path" />

    <flux:heading size="xl" level="1" class="mt-2">{{ $item->name }}</flux:heading>

    <flux:heading size="lg" level="2" class="mt-6">{{ __('items.place') }}</flux:heading>

    <flux:card class="mt-2">
        @foreach ($this->path as $place)
            @unless ($loop->first)
                <span aria-hidden="true" class="text-zinc-500">›</span>
            @endunless

            <flux:link :href="route('places.show', [$household, $place])" wire:navigate wire:key="path-{{ $place->id }}">{{ $place->name }}</flux:link>
        @endforeach
    </flux:card>

    <flux:heading size="lg" level="2" class="mt-6">{{ __('items.notes') }}</flux:heading>

    <flux:card class="mt-2">
        @if ($item->notes === null)
            <flux:text>{{ __('items.no_notes') }}</flux:text>
        @else
            <p>{{ $item->notes }}</p>
        @endif
    </flux:card>
</section>
