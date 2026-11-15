<div class="space-y-4">
    <div>
        <flux:heading size="lg">{{ __('places.move_heading', ['name' => $place->name]) }}</flux:heading>
        <flux:text class="mt-1">{{ __('places.now_in', ['place' => $this->whereItIsNow]) }}</flux:text>
    </div>

    <flux:input
        wire:model.live.debounce.300ms="search"
        :label="__('places.find_a_place')"
        icon="magnifying-glass"
        autocomplete="off"
    />

    @if ($this->isSearching())
        @if ($this->matches->isEmpty())
            <flux:text>{{ __('places.no_match', ['search' => $search]) }}</flux:text>
        @else
            <ul class="max-h-80 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                @foreach ($this->matches as $match)
                    <li wire:key="picker-match-{{ $match->id }}" class="flex border-b border-zinc-200 px-4 last:border-b-0 dark:border-zinc-700">
                        <x-places.picker-choice
                            :name="$match->name"
                            :path="$this->tree->pathOf($match)"
                            :is-chosen="$match->is($this->chosenPlace)"
                            :can-be-chosen="$this->tree->canBeMovedTo($place, $match)"
                            wire:click="choosePlace('{{ $match->id }}')"
                        />
                    </li>
                @endforeach
            </ul>
        @endif
    @else
        <ul class="max-h-80 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700" x-data="{ open: true }">
            <li>
                <x-places.tree-row :name="$this->householdName" :depth="0" :has-children="$this->tree->roots()->isNotEmpty()">
                    <x-places.picker-choice
                        :name="$this->householdName"
                        :is-chosen="$isHouseholdChosen"
                        :can-be-chosen="$this->tree->canBeMovedTo($place, null)"
                        wire:click="chooseHousehold"
                    />
                </x-places.tree-row>

                <ul x-show="open">
                    @foreach ($this->tree->roots() as $topPlace)
                        <x-places.picker-node
                            :place="$topPlace"
                            :tree="$this->tree"
                            :depth="1"
                            :place-to-move="$place"
                            :chosen-place="$this->chosenPlace"
                            wire:key="picker-place-{{ $topPlace->id }}"
                        />
                    @endforeach
                </ul>
            </li>
        </ul>
    @endif

    <flux:text size="sm">
        {{ __('places.cannot_be_chosen', ['name' => $place->name]) }}
        <br>
        @if ($isHouseholdChosen)
            {{ __('places.selected', ['place' => $this->householdName]) }}
        @elseif ($this->chosenPlace !== null)
            {{ __('places.selected', ['place' => $this->tree->pathOf($this->chosenPlace)]) }}
        @else
            {{ __('places.choose_a_place_first') }}
        @endif
    </flux:text>

    <div class="flex gap-3">
        <flux:button wire:click="confirm" variant="primary" :disabled="! $this->hasChoice">{{ __('places.move_here') }}</flux:button>

        <flux:modal.close>
            <flux:button variant="ghost">{{ __('places.cancel') }}</flux:button>
        </flux:modal.close>
    </div>
</div>
