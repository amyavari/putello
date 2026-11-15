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
use Livewire\Attributes\Computed;
use Livewire\Component;

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

    public function rename(RenamePlaceAction $renamePlace): void
    {
        $this->authorize('edit', $this->household);

        $this->validatePlaceName(
            PlaceTree::for($this->household)->parentOf($this->place),
            except: $this->place,
        );

        $renamePlace->handle($this->place, $this->name);

        unset($this->tree);

        $this->modal('rename-place')->close();

        $this->message = __('places.renamed', ['name' => $this->place->name]);
    }

    public function delete(DeletePlaceAction $deletePlace): void
    {
        $this->authorize('edit', $this->household);

        $tree = PlaceTree::for($this->household);
        $placesInside = $tree->childrenOf($this->place);

        if ($placesInside->isNotEmpty()) {
            $this->addError('place', trans_choice('places.move_places_inside_first', $placesInside->count()));

            return;
        }

        $parent = $tree->parentOf($this->place);

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
