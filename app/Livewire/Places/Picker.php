<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Livewire\Concerns\ShowsHouseholdName;
use App\Models\Household;
use App\Models\Place;
use App\Support\PlaceTree;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read PlaceTree $tree
 * @property-read string $householdName
 * @property-read Place|null $chosenPlace
 * @property-read bool $hasChoice
 * @property-read string $whereItIsNow
 * @property-read Collection<int, Place> $matches
 */
final class Picker extends Component
{
    use ShowsHouseholdName;

    public Household $household;

    /**
     * The place being moved.
     */
    public Place $place;

    public string $search = '';

    public ?string $chosenPlaceId = null;

    public bool $isHouseholdChosen = false;

    #[Computed]
    public function tree(): PlaceTree
    {
        return PlaceTree::for($this->household);
    }

    #[Computed]
    public function chosenPlace(): ?Place
    {
        return $this->findPlace($this->chosenPlaceId);
    }

    #[Computed]
    public function hasChoice(): bool
    {
        return $this->isHouseholdChosen || $this->chosenPlace instanceof Place;
    }

    #[Computed]
    public function whereItIsNow(): string
    {
        return $this->tree->parentOf($this->place)->name ?? $this->householdName;
    }

    /**
     * @return Collection<int, Place>
     */
    #[Computed]
    public function matches(): Collection
    {
        return $this->tree->placesNamedLike($this->trimmedSearch());
    }

    public function choosePlace(string $placeId): void
    {
        $place = $this->findPlace($placeId);

        if (! $place instanceof Place || ! $this->tree->canBeMovedTo($this->place, $place)) {
            return;
        }

        $this->chosenPlaceId = $place->id;
        $this->isHouseholdChosen = false;
    }

    public function chooseHousehold(): void
    {
        if (! $this->tree->canBeMovedTo($this->place, null)) {
            return;
        }

        $this->chosenPlaceId = null;
        $this->isHouseholdChosen = true;
    }

    public function confirm(): void
    {
        if (! $this->hasChoice) {
            return;
        }

        $this->dispatch('place-chosen', placeId: $this->chosenPlace?->id);
    }

    public function isSearching(): bool
    {
        return $this->trimmedSearch() !== '';
    }

    public function render(): View
    {
        return view('livewire.places.picker');
    }

    private function findPlace(?string $placeId): ?Place
    {
        if ($placeId === null) {
            return null;
        }

        return $this->household->places()->find($placeId);
    }

    private function trimmedSearch(): string
    {
        return Str::trim($this->search);
    }
}
