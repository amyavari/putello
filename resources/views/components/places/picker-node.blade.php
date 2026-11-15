@props(['place', 'picker', 'depth'])

@php($children = $picker->tree->placesInside($place))

<li {{ $attributes }} x-data="{ open: false }">
    <x-places.tree-row :name="$place->name" :$depth :has-children="$children->isNotEmpty()">
        <x-places.picker-choice
            :name="$place->name"
            :is-chosen="$place->is($picker->chosenPlace)"
            :can-be-chosen="$picker->canBeChosen($place)"
            wire:click="choosePlace('{{ $place->id }}')"
        />
    </x-places.tree-row>

    @if ($children->isNotEmpty())
        <ul x-show="open">
            @foreach ($children as $child)
                <x-places.picker-node :place="$child" :$picker :depth="$depth + 1" wire:key="picker-place-{{ $child->id }}" />
            @endforeach
        </ul>
    @endif
</li>
