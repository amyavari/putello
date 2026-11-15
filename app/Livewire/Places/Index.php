<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Models\Household;
use App\Support\PlaceTree;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Index extends Component
{
    public Household $household;

    public ?string $message = null;

    public function mount(): void
    {
        $this->message = session('message');
    }

    #[Computed]
    public function householdName(): string
    {
        return $this->household->nameFor(Auth::user());
    }

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
