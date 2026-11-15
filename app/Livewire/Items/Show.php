<?php

declare(strict_types=1);

namespace App\Livewire\Items;

use App\Livewire\Concerns\ShowsHouseholdName;
use App\Models\Household;
use App\Models\Item;
use App\Models\Place;
use App\Support\PlaceTree;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read string $householdName
 * @property-read Collection<int, Place> $path
 */
final class Show extends Component
{
    use ShowsHouseholdName;

    public Household $household;

    public Item $item;

    /**
     * The places from the top down to the place of the item.
     *
     * @return Collection<int, Place>
     */
    #[Computed]
    public function path(): Collection
    {
        $place = $this->item->place;

        return PlaceTree::for($this->household)
            ->placesAbove($place)
            ->push($place);
    }

    public function render(): View
    {
        return view('livewire.items.show')
            ->title($this->item->name);
    }
}
