@props(['household'])

{{-- The app name, with the household of the page under it (its name, or the user's label for it). --}}
<div class="px-2 py-1">
    <p class="text-lg font-bold text-ink">{{ config('app.name') }}</p>

    @if ($household)
        <p class="truncate text-sm text-ink-muted">
            @if ($household->canBeEditedBy(auth()->user()))
                {{ $household->nameFor(auth()->user()) }}
            @else
                {{ __('shell.household_of_viewer', ['household' => $household->nameFor(auth()->user())]) }}
            @endif
        </p>
    @endif
</div>
