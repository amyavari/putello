@props(['household', 'places'])

{{-- The path of the item's place, each place a link. --}}
<p class="flex flex-wrap items-center gap-x-2">
    @foreach ($places as $place)
        @unless ($loop->first)
            <span aria-hidden="true" class="text-ink-muted">›</span>
        @endunless

        <a
            href="{{ route('places.show', [$household, $place]) }}"
            wire:navigate
            wire:key="path-{{ $place->id }}"
            class="text-accent underline underline-offset-2 hover:text-accent-hover"
        >{{ $place->name }}</a>
    @endforeach
</p>
