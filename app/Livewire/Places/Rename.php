<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Actions\RenamePlaceAction;
use App\Livewire\Concerns\ValidatesPlaceName;
use App\Models\Household;
use App\Models\Place;
use App\Support\PlaceTree;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class Rename extends Component
{
    use ValidatesPlaceName;

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

    public function rename(RenamePlaceAction $renamePlace): void
    {
        $this->authorize('edit', $this->household);

        $parent = PlaceTree::for($this->household)->parentOf($this->place);

        $this->validatePlaceName($parent, except: $this->place);

        $renamePlace->handle($this->place, $this->name);

        $this->modal('rename-place')->close();

        $this->dispatch('place-renamed');
    }

    public function render(): View
    {
        return view('livewire.places.rename');
    }
}
