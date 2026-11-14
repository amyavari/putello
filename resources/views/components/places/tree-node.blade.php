@props(['place', 'tree', 'depth'])

@php($children = $tree->childrenOf($place))

<li {{ $attributes }} x-data="{ open: true }">
    <x-places.tree-row :name="$place->name" :$depth :has-children="$children->isNotEmpty()" />

    @if ($children->isNotEmpty())
        <ul x-show="open">
            @foreach ($children as $child)
                <x-places.tree-node :place="$child" :$tree :depth="$depth + 1" wire:key="place-{{ $child->id }}" />
            @endforeach
        </ul>
    @endif
</li>
