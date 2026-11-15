<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Actions\CreatePlaceAction;
use App\Livewire\Concerns\RedirectsToPlaces;
use App\Livewire\Concerns\ShowsHouseholdName;
use App\Livewire\Concerns\ValidatesPlaceName;
use App\Models\Household;
use App\Models\Place;
use App\Support\PlaceTree;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

final class Create extends Component
{
    use RedirectsToPlaces, ShowsHouseholdName, ValidatesPlaceName;

    public Household $household;

    /**
     * The place the new place goes inside, or null for the top of the household.
     */
    public ?Place $parent = null;

    public string $name = '';

    public function mount(Request $request): void
    {
        $this->authorize('edit', $this->household);

        if ($request->filled('inside')) {
            $this->parent = $this->household->places()->findOrFail(
                $request->string('inside')->toString(),
            );
        }
    }

    /**
     * @return Collection<int, Place>
     */
    #[Computed]
    public function placesAbove(): Collection
    {
        if (! $this->parent instanceof Place) {
            return new Collection;
        }

        return PlaceTree::for($this->household)
            ->placesAbove($this->parent)
            ->push($this->parent);
    }

    /**
     * $placeId null is the household.
     */
    #[On('place-chosen')]
    public function useChosenPlace(?string $placeId): void
    {
        $place = $placeId === null ? null : $this->household->places()->find($placeId);

        if ($placeId !== null && ! $place instanceof Place) {
            $this->addError('placeId', __('places.cannot_choose_place'));

            return;
        }

        $this->parent = $place;

        $this->modal('choose-place')->close();
    }

    public function add(CreatePlaceAction $createPlace): void
    {
        $this->authorize('edit', $this->household);

        $this->validatePlaceName($this->parent);

        $place = $createPlace->handle($this->household, $this->parent, $this->name);

        $this->redirectToPlaces(
            $this->household,
            __('places.added', ['name' => $place->name]),
            $this->parent,
        );
    }

    public function render(): View
    {
        return view('livewire.places.create')
            ->title(__('places.add'));
    }
}
