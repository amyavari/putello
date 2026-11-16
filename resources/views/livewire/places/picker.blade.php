<div class="space-y-4">
    <div>
        @if ($this->isMoving())
            <flux:heading size="lg" class="font-semibold">{{ __('places.move_heading', ['name' => $this->nameOfWhatIsMoved()]) }}</flux:heading>
            <p class="mt-1 text-sm text-ink-muted">{{ __('places.now_in', ['place' => $this->whereItIsNow]) }}</p>
        @else
            <flux:heading size="lg" class="font-semibold">{{ __('places.choose_a_place') }}</flux:heading>
        @endif
    </div>

    <flux:input
        wire:model.live.debounce.300ms="search"
        :label="__('places.find_a_place')"
        icon="magnifying-glass"
        autocomplete="off"
    />

    @if ($this->isSearching())
        @if ($this->matches->isEmpty())
            <p class="text-ink-muted">{{ __('places.no_match', ['search' => $search]) }}</p>
        @else
            <ul class="max-h-80 overflow-y-auto rounded-lg border border-border">
                @foreach ($this->matches as $match)
                    <li wire:key="picker-match-{{ $match->id }}" class="flex border-b border-border px-4 last:border-b-0 has-[[aria-pressed=true]]:bg-accent-soft">
                        <x-places.picker-choice
                            :name="$match->name"
                            :path="$this->tree->pathOf($match)"
                            :is-chosen="$match->is($this->chosenPlace)"
                            :can-be-chosen="$this->canBeChosen($match)"
                            wire:click="choosePlace('{{ $match->id }}')"
                        />
                    </li>
                @endforeach
            </ul>
        @endif
    @else
        <ul class="max-h-80 overflow-y-auto rounded-lg border border-border" x-data="{ open: true }">
            <li>
                <x-places.tree-row :name="$this->householdName" :depth="0" :has-children="$this->tree->topPlaces()->isNotEmpty()">
                    <x-places.picker-choice
                        :name="$this->householdName"
                        :is-chosen="$isHouseholdChosen"
                        :can-be-chosen="$this->canBeChosen(null)"
                        wire:click="chooseHousehold"
                    />
                </x-places.tree-row>

                <ul x-show="open">
                    @foreach ($this->tree->topPlaces() as $topPlace)
                        <x-places.picker-node
                            :place="$topPlace"
                            :picker="$this"
                            :depth="1"
                            wire:key="picker-place-{{ $topPlace->id }}"
                        />
                    @endforeach
                </ul>
            </li>
        </ul>
    @endif

    <p class="text-sm text-ink-muted">
        @if ($this->isMovingAPlace())
            {{ __('places.cannot_be_chosen', ['name' => $place->name]) }}
            <br>
        @endif

        @if ($isHouseholdChosen)
            {{ __('places.selected', ['place' => $this->householdName]) }}
        @elseif ($this->chosenPlace !== null)
            {{ __('places.selected', ['place' => $this->tree->pathOf($this->chosenPlace)]) }}
        @else
            {{ __('places.choose_a_place_first') }}
        @endif
    </p>

    <x-modal.actions>
        <flux:button wire:click="confirm" variant="primary" :disabled="! $this->hasChoice">{{ $this->isMoving() ? __('places.move_here') : __('places.choose') }}</flux:button>
    </x-modal.actions>
</div>
