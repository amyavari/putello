<?php

declare(strict_types=1);

namespace App\Livewire\Households;

use App\Actions\CreateHouseholdAction;
use App\Models\Household;
use App\Models\User;
use Flux\Flux;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class Index extends Component
{
    #[Validate(['required', 'string', 'max:255'])]
    public string $name = '';

    public ?string $message = null;

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
     * Add a household owned by the current user.
     */
    public function add(#[CurrentUser] User $user, CreateHouseholdAction $createHousehold): void
    {
        $this->name = Str::trim($this->name);

        $this->validate();

        $household = $createHousehold->handle($user, $this->name);

        $this->reset('name');
        unset($this->households);
        Flux::modal('add-household')->close();

        $this->message = __('households.added', ['name' => $household->name]);
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
