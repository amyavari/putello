@props(['household', 'householdName', 'places'])

<flux:breadcrumbs>
    <flux:breadcrumbs.item :href="route('places.index', $household)" wire:navigate>
        {{ $householdName }}
    </flux:breadcrumbs.item>

    @foreach ($places as $place)
        <flux:breadcrumbs.item :href="route('places.show', [$household, $place])" wire:navigate wire:key="crumb-{{ $place->id }}">
            {{ $place->name }}
        </flux:breadcrumbs.item>
    @endforeach
</flux:breadcrumbs>
