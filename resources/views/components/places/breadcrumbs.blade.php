@props(['household', 'householdName', 'places', 'item' => null])

{{-- The pages above this one: the household first, then its places, then the item. --}}
<nav aria-label="{{ __('shell.breadcrumb') }}" class="flex flex-wrap items-center gap-x-2 text-sm">
    <x-places.crumb :href="route('places.index', $household)">{{ $householdName }}</x-places.crumb>

    @foreach ($places as $place)
        <x-places.crumb :href="route('places.show', [$household, $place])" wire:key="crumb-{{ $place->id }}">{{ $place->name }}</x-places.crumb>
    @endforeach

    @if ($item !== null)
        <x-places.crumb :href="route('items.show', [$household, $item])">{{ $item->name }}</x-places.crumb>
    @endif
</nav>
