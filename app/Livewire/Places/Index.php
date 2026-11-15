<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Livewire\Concerns\ShowsFlashMessage;
use App\Livewire\Concerns\ShowsHouseholdName;
use App\Models\Household;
use App\Support\PlaceTree;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Index extends Component
{
    use ShowsFlashMessage, ShowsHouseholdName;

    public Household $household;

    #[Computed]
    public function tree(): PlaceTree
    {
        return PlaceTree::for($this->household);
    }

    public function render(): View
    {
        return view('livewire.places.index')
            ->title(__('places.title'));
    }
}
