<?php

declare(strict_types=1);

namespace App\Livewire\Households;

use App\Models\Household;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Index extends Component
{
    /**
     * Get the households of the current user, with their member counts.
     *
     * @return Collection<int, Household>
     */
    #[Computed]
    public function households(): Collection
    {
        return Auth::user()
            ->households()
            ->withCount('members')
            ->get();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.households.index')
            ->title(__('households.title'));
    }
}
