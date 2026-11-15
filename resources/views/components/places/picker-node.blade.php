@props(['place', 'tree', 'depth', 'placeToMove', 'chosenPlace'])

@php($children = $tree->childrenOf($place))

<li {{ $attributes }} x-data="{ open: false }">
    <x-places.tree-row :name="$place->name" :$depth :has-children="$children->isNotEmpty()">
        <x-places.picker-choice
            :name="$place->name"
            :is-chosen="$place->is($chosenPlace)"
            :can-be-chosen="$tree->canBeMovedTo($placeToMove, $place)"
            wire:click="choosePlace('{{ $place->id }}')"
        />
    </x-places.tree-row>

    @if ($children->isNotEmpty())
        <ul x-show="open">
            @foreach ($children as $child)
                <x-places.picker-node :place="$child" :$tree :depth="$depth + 1" :$placeToMove :$chosenPlace wire:key="picker-place-{{ $child->id }}" />
            @endforeach
        </ul>
    @endif
</li>
