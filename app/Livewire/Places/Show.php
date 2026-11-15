<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Actions\DeletePlaceAction;
use App\Actions\MovePlaceAction;
use App\Actions\RestoreItemAction;
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
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * @property-read PlaceTree $tree
 * @property-read string $householdName
 * @property-read Collection<int, Item> $itemsInside
 * @property-read Collection<int, Place> $placesInside
 * @property-read bool $hasPlacesInside
 * @property-read bool $isEmpty
 * @property-read string $notEmptyErrorMessage
 * @property-read Place|null $parentPlace
 */
final class Show extends Component
{
    use RedirectsToPlaces, ShowsFlashMessage, ShowsHouseholdName;

    public Household $household;

    public Place $place;

    /**
     * The item archived just before this page opened, so the message can offer Undo.
     */
    #[Locked]
    public ?string $archivedItemId = null;

    public function mount(): void
    {
        $this->archivedItemId = session('archived_item_id');
    }

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

    /**
     * Only the items and places directly inside count. Deeper ones are inside those places.
     */
    #[Computed]
    public function isEmpty(): bool
    {
        return $this->itemsInside->isEmpty() && ! $this->hasPlacesInside;
    }

    #[Computed]
    public function notEmptyErrorMessage(): string
    {
        $numberOfItems = $this->itemsInside->count();
        $numberOfPlaces = $this->placesInside->count();

        if ($numberOfPlaces === 0) {
            return trans_choice('places.move_things_inside_first.items', $numberOfItems);
        }

        if ($numberOfItems === 0) {
            return trans_choice('places.move_things_inside_first.places', $numberOfPlaces);
        }

        return __('places.move_things_inside_first.items_and_places', [
            'items' => trans_choice('places.number_of_items', $numberOfItems),
            'places' => trans_choice('places.number_of_places', $numberOfPlaces),
        ]);
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

        $this->archivedItemId = null;

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

    public function undoArchive(RestoreItemAction $restoreItem): void
    {
        $this->authorize('edit', $this->household);

        $item = $this->household->items()
            ->onlyTrashed()
            ->findOrFail($this->archivedItemId);

        $restoreItem->handle($item, $this->place);

        $this->archivedItemId = null;

        unset($this->itemsInside);

        $this->message = __('items.restored', [
            'name' => $item->name,
            'place' => $this->place->name,
        ]);
    }

    public function delete(DeletePlaceAction $deletePlace): void
    {
        $this->authorize('edit', $this->household);

        if (! $this->isEmpty) {
            $this->addError('place', $this->notEmptyErrorMessage);

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
