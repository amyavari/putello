<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Actions\RenamePlaceAction;
use App\Models\Household;
use App\Models\Place;
use App\Support\PlaceTree;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Show extends Component
{
    public Household $household;

    public Place $place;

    public ?string $message = null;

    /**
     * The new name typed in the Rename place modal.
     */
    public string $name = '';

    /**
     * Show the message the previous page left, if any, and fill the Rename place modal with the current name.
     */
    public function mount(): void
    {
        $this->message = session('message');
        $this->name = $this->place->name;
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
     * Get the places of the household as a tree.
     */
    #[Computed]
    public function tree(): PlaceTree
    {
        return PlaceTree::for($this->household);
    }

    /**
     * Rename the place, then close the modal and show a message.
     */
    public function rename(RenamePlaceAction $renamePlace): void
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

        $renamePlace->handle($this->place, $this->name);

        unset($this->tree);

        $this->modal('rename-place')->close();

        $this->message = __('places.renamed', ['name' => $this->place->name]);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.places.show')
            ->title($this->place->name);
    }

    /**
     * Fail when another place next to this one already has the name, ignoring case.
     *
     * The check runs in PHP, so it is the same on SQLite and PostgreSQL.
     */
    private function ensureNameIsFreeAmongSiblings(string $attribute, string $value, Closure $fail): void
    {
        $tree = PlaceTree::for($this->household);
        $parent = $tree->ancestorsOf($this->place)->last();

        $siblings = $parent instanceof Place
            ? $tree->childrenOf($parent)
            : $tree->roots();

        $isTaken = $siblings
            ->reject(fn (Place $sibling): bool => $sibling->is($this->place))
            ->contains(fn (Place $sibling): bool => Str::lower($sibling->name) === Str::lower($value));

        if ($isTaken) {
            $fail('places.name_taken')->translate(['name' => $value]);
        }
    }
}
