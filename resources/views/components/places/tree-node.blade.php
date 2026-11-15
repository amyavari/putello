@props(['household', 'place', 'tree', 'depth'])

@php($children = $tree->placesInside($place))

<li {{ $attributes }} x-data="{ open: true }">
    <x-places.tree-row :name="$place->name" :href="route('places.show', [$household, $place])" :$depth :has-children="$children->isNotEmpty()" :number-of-items="$tree->numberOfItemsInside($place)" />

    @if ($children->isNotEmpty())
        <ul x-show="open">
            @foreach ($children as $child)
                <x-places.tree-node :$household :place="$child" :$tree :depth="$depth + 1" wire:key="place-{{ $child->id }}" />
            @endforeach
        </ul>
    @endif
</li>
