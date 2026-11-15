<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Actions\DeletePlaceAction;
use App\Actions\MovePlaceAction;
use App\Livewire\Concerns\RedirectsToPlaces;
use App\Livewire\Concerns\ShowsFlashMessage;
use App\Livewire\Concerns\ShowsHouseholdName;
use App\Models\Household;
use App\Models\Item;
use App\Models\Place;
use App\Support\PlaceTree;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * @property-read PlaceTree $tree
 * @property-read string $householdName
 * @property-read Collection<int, Item> $itemsInside
 * @property-read Collection<int, Place> $placesInside
 * @property-read bool $hasPlacesInside
 * @property-read string $placesInsideErrorMessage
 * @property-read Place|null $parentPlace
 */
final class Show extends Component
{
    use RedirectsToPlaces, ShowsFlashMessage, ShowsHouseholdName;

    public Household $household;

    public Place $place;

    #[Computed]
    public function tree(): PlaceTree
    {
        return PlaceTree::for($this->household);
    }

    /**
     * Only the items directly in this place, sorted by name ignoring case.
     *
     * @return Collection<int, Item>
     */
    #[Computed]
    public function itemsInside(): Collection
    {
        return $this->place->items()
            ->get()
            ->sortBy(fn (Item $item): string => Str::lower($item->name))
            ->values();
    }

    /**
     * @return Collection<int, Place>
     */
    #[Computed]
    public function placesInside(): Collection
    {
        return $this->tree->placesInside($this->place);
    }

    #[Computed]
    public function hasPlacesInside(): bool
    {
        return $this->placesInside->isNotEmpty();
    }

    #[Computed]
    public function placesInsideErrorMessage(): string
    {
        return trans_choice('places.move_places_inside_first', $this->placesInside->count());
    }

    #[Computed]
    public function parentPlace(): ?Place
    {
        return $this->tree->parentOf($this->place);
    }

    #[On('place-renamed')]
    public function showRenamedPlace(): void
    {
        $this->place->refresh();

        unset($this->tree);

        $this->message = __('places.renamed', ['name' => $this->place->name]);
    }

    /**
     * $placeId null is the top of the household.
     */
    #[On('place-chosen')]
    public function move(MovePlaceAction $movePlace, ?string $placeId): void
    {
        $this->authorize('edit', $this->household);

        $newParent = $placeId === null ? null : $this->household->places()->find($placeId);

        $error = $this->moveErrorMessage($placeId, $newParent);

        if ($error !== null) {
            $this->addError('placeId', $error);

            return;
        }

        $movePlace->handle($this->place, $newParent);

        $this->redirectToPlaces(
            $this->household,
            __('places.moved', [
                'name' => $this->place->name,
                'place' => $newParent->name ?? $this->householdName,
            ]),
            $this->place,
        );
    }

    public function delete(DeletePlaceAction $deletePlace): void
    {
        $this->authorize('edit', $this->household);

        if ($this->hasPlacesInside) {
            $this->addError('place', $this->placesInsideErrorMessage);

            return;
        }

        $parent = $this->parentPlace;

        $deletePlace->handle($this->place);

        $this->redirectToPlaces(
            $this->household,
            __('places.deleted', ['name' => $this->place->name]),
            $parent,
        );
    }

    public function render(): View
    {
        return view('livewire.places.show')
            ->title($this->place->name);
    }

    private function moveErrorMessage(?string $placeId, ?Place $newParent): ?string
    {
        $isUnknownPlace = $placeId !== null && ! $newParent instanceof Place;

        if ($isUnknownPlace || ! $this->tree->canBeMovedTo($this->place, $newParent)) {
            return __('places.cannot_move_there', ['name' => $this->place->name]);
        }

        if ($this->tree->hasSiblingNamed($newParent, $this->place->name, except: $this->place)) {
            return __('places.name_taken', ['name' => $this->place->name]);
        }

        return null;
    }
}
