<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Livewire\Concerns\ShowsHouseholdName;
use App\Models\Household;
use App\Models\Item;
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
     * The place being moved, or null when the picker only chooses a place (Add place).
     */
    public ?Place $place = null;

    /**
     * The item being moved, or null when no item is moved.
     */
    public ?Item $item = null;

    /**
     * True when the place is for a new item (Add item), so the household can't be chosen.
     */
    public bool $isForAnItem = false;

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
        if ($this->item instanceof Item) {
            return $this->item->place->name;
        }

        if (! $this->place instanceof Place) {
            return '';
        }

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

        if (! $place instanceof Place || ! $this->canBeChosen($place)) {
            return;
        }

        $this->chosenPlaceId = $place->id;
        $this->isHouseholdChosen = false;
    }

    public function chooseHousehold(): void
    {
        if (! $this->canBeChosen(null)) {
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

    public function isMovingAPlace(): bool
    {
        return $this->place instanceof Place;
    }

    public function isMovingAnItem(): bool
    {
        return $this->item instanceof Item;
    }

    public function isMoving(): bool
    {
        return $this->isMovingAPlace() || $this->isMovingAnItem();
    }

    public function nameOfWhatIsMoved(): string
    {
        return $this->item->name ?? $this->place->name ?? '';
    }

    /**
     * $place null is the household.
     */
    public function canBeChosen(?Place $place): bool
    {
        if (! $place instanceof Place && ! $this->canChooseTheHousehold()) {
            return false;
        }

        if ($this->item instanceof Item) {
            return ! $this->item->place->is($place);
        }

        if ($this->place instanceof Place) {
            return $this->tree->canBeMovedTo($this->place, $place);
        }

        return true;
    }

    public function isSearching(): bool
    {
        return $this->trimmedSearch() !== '';
    }

    public function render(): View
    {
        return view('livewire.places.picker');
    }

    private function canChooseTheHousehold(): bool
    {
        return ! $this->isForAnItem && ! $this->isMovingAnItem();
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
