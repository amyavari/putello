<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Actions\CreatePlaceAction;
use App\Livewire\Concerns\ValidatesPlaceName;
use App\Models\Household;
use App\Models\Place;
use App\Support\PlaceTree;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Create extends Component
{
    use ValidatesPlaceName;

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

    #[Computed]
    public function householdName(): string
    {
        return $this->household->nameFor(Auth::user());
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
            ->ancestorsOf($this->parent)
            ->push($this->parent);
    }

    public function add(CreatePlaceAction $createPlace): void
    {
        $this->authorize('edit', $this->household);

        $this->validatePlaceName($this->parent);

        $place = $createPlace->handle($this->household, $this->parent, $this->name);

        session()->flash('message', __('places.added', ['name' => $place->name]));

        if ($this->parent instanceof Place) {
            $this->redirectRoute('places.show', [$this->household, $this->parent], navigate: true);

            return;
        }

        $this->redirectRoute('places.index', $this->household, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.places.create')
            ->title(__('places.add'));
    }
}
