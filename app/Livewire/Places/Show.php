<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Models\Household;
use App\Models\Place;
use App\Support\PlaceTree;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Show extends Component
{
    public Household $household;

    public Place $place;

    public ?string $message = null;

    /**
     * Show the message the previous page left, if any.
     */
    public function mount(): void
    {
        $this->message = session('message');
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
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.places.show')
            ->title($this->place->name);
    }
}
