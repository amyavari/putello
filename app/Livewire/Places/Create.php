<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Actions\CreatePlaceAction;
use App\Models\Household;
use App\Models\Place;
use App\Support\PlaceTree;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Create extends Component
{
    public Household $household;

    /**
     * The place the new place goes inside, or null for the top of the household.
     */
    public ?Place $parent = null;

    public string $name = '';

    /**
     * Check that the user can add places, and find the place given in the "inside" query parameter.
     */
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
     * Get the household's name as the current user sees it: their label if they set one, otherwise the name.
     */
    #[Computed]
    public function householdName(): string
    {
        return $this->household->nameFor(Auth::user());
    }

    /**
     * Get the places above the new place, from the top down: the parent and its ancestors.
     *
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

    /**
     * Add the place, then open the page of the place it went inside (or Places) with a message.
     */
    public function add(CreatePlaceAction $createPlace): void
    {
        $this->authorize('edit', $this->household);

        $this->name = Str::trim($this->name);

        $this->validate(
            rules: [
                'name' => [
                    'bail',
                    'required',
                    'string',
                    'max:255',
                    $this->ensureNameIsFreeAmongSiblings(...),
                ],
            ],
            messages: [
                'name.required' => __('places.name_required'),
            ],
        );

        $place = $createPlace->handle($this->household, $this->parent, $this->name);

        session()->flash('message', __('places.added', ['name' => $place->name]));

        if ($this->parent instanceof Place) {
            $this->redirectRoute('places.show', [$this->household, $this->parent], navigate: true);

            return;
        }

        $this->redirectRoute('places.index', $this->household, navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.places.create')
            ->title(__('places.add'));
    }

    /**
     * Fail when a sibling place already has the name, ignoring case.
     *
     * The check runs in PHP, so it is the same on SQLite and PostgreSQL.
     */
    private function ensureNameIsFreeAmongSiblings(string $attribute, string $value, Closure $fail): void
    {
        $tree = PlaceTree::for($this->household);

        $siblings = $this->parent instanceof Place
            ? $tree->childrenOf($this->parent)
            : $tree->roots();

        $isTaken = $siblings->contains(fn (Place $sibling): bool => Str::lower($sibling->name) === Str::lower($value));

        if ($isTaken) {
            $fail('places.name_taken')->translate(['name' => $value]);
        }
    }
}
