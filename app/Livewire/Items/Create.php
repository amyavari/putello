<?php

declare(strict_types=1);

namespace App\Livewire\Items;

use App\Actions\CreateItemAction;
use App\Livewire\Concerns\ShowsHouseholdName;
use App\Livewire\Concerns\ValidatesItem;
use App\Models\Household;
use App\Models\Place;
use App\Models\User;
use App\Support\PlaceTree;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * @property-read string $householdName
 * @property-read Collection<int, Place> $path
 */
final class Create extends Component
{
    use ShowsHouseholdName, ValidatesItem;

    public Household $household;

    /**
     * The place the new item goes in, or null until one is chosen.
     */
    public ?Place $place = null;

    public function mount(Request $request): void
    {
        $this->authorize('edit', $this->household);

        $this->name = $request->string('name')->toString();

        if ($request->filled('inside')) {
            $this->place = $this->household->places()->findOrFail(
                $request->string('inside')->toString(),
            );
        }
    }

    /**
     * @return Collection<int, Place>
     */
    #[Computed]
    public function path(): Collection
    {
        if (! $this->place instanceof Place) {
            return new Collection;
        }

        return PlaceTree::for($this->household)->placesDownTo($this->place);
    }

    /**
     * $placeId null is the household, which can't hold items.
     */
    #[On('place-chosen')]
    public function useChosenPlace(?string $placeId): void
    {
        $place = $this->household->places()->find($placeId);

        if (! $place instanceof Place) {
            $this->addError('placeId', __('places.cannot_choose_place'));

            return;
        }

        $this->place = $place;

        $this->modal('choose-place')->close();
    }

    public function add(#[CurrentUser] User $user, CreateItemAction $createItem): void
    {
        $this->authorize('edit', $this->household);

        $this->validateItem();
        $this->ensurePlaceIsChosen();

        $item = $createItem->handle($user, $this->place, $this->name, $this->notes);

        session()->flash('message', __('items.added', ['name' => $item->name]));

        $this->redirectRoute('items.show', [$this->household, $item], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.items.create')
            ->title(__('items.add'));
    }

    /**
     * @phpstan-assert Place $this->place
     */
    private function ensurePlaceIsChosen(): void
    {
        if (! $this->place instanceof Place) {
            throw ValidationException::withMessages([
                'placeId' => __('items.place_required'),
            ]);
        }
    }
}
