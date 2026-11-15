<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Actions\DeletePlaceAction;
use App\Actions\RenamePlaceAction;
use App\Livewire\Concerns\RedirectsToPlaces;
use App\Livewire\Concerns\ShowsFlashMessage;
use App\Livewire\Concerns\ShowsHouseholdName;
use App\Livewire\Concerns\ValidatesPlaceName;
use App\Models\Household;
use App\Models\Place;
use App\Support\PlaceTree;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read PlaceTree $tree
 * @property-read Collection<int, Place> $placesInside
 * @property-read bool $hasPlacesInside
 * @property-read string $placesInsideErrorMessage
 * @property-read Place|null $parentPlace
 */
final class Show extends Component
{
    use RedirectsToPlaces, ShowsFlashMessage, ShowsHouseholdName, ValidatesPlaceName;

    public Household $household;

    public Place $place;

    /**
     * The new name typed in the Rename place modal.
     */
    public string $name = '';

    public function mount(): void
    {
        $this->name = $this->place->name;
    }

    #[Computed]
    public function tree(): PlaceTree
    {
        return PlaceTree::for($this->household);
    }

    /**
     * @return Collection<int, Place>
     */
    #[Computed]
    public function placesInside(): Collection
    {
        return $this->tree->childrenOf($this->place);
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

    public function rename(RenamePlaceAction $renamePlace): void
    {
        $this->authorize('edit', $this->household);

        $this->validatePlaceName($this->parentPlace, except: $this->place);

        $renamePlace->handle($this->place, $this->name);

        unset($this->tree);

        $this->modal('rename-place')->close();

        $this->message = __('places.renamed', ['name' => $this->place->name]);
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
}
