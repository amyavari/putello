<?php

declare(strict_types=1);

namespace App\Livewire\Items;

use App\Actions\ArchiveItemAction;
use App\Actions\MoveItemAction;
use App\Livewire\Concerns\ShowsFlashMessage;
use App\Livewire\Concerns\ShowsHouseholdName;
use App\Models\Household;
use App\Models\Item;
use App\Models\Place;
use App\Models\User;
use App\Support\PlaceTree;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * @property-read string $householdName
 * @property-read Collection<int, Place> $path
 */
final class Show extends Component
{
    use ShowsFlashMessage, ShowsHouseholdName;

    public Household $household;

    public Item $item;

    /**
     * @return Collection<int, Place>
     */
    #[Computed]
    public function path(): Collection
    {
        return PlaceTree::for($this->household)->placesDownTo($this->item->place);
    }

    /**
     * $placeId null is the household, which can't hold items.
     */
    #[On('place-chosen')]
    public function move(#[CurrentUser] User $user, MoveItemAction $moveItem, ?string $placeId): void
    {
        $this->authorize('edit', $this->household);

        $newPlace = $this->household->places()->find($placeId);

        if (! $newPlace instanceof Place || $this->isAlreadyIn($newPlace)) {
            $this->addError('placeId', __('items.cannot_move_there', ['name' => $this->item->name]));

            return;
        }

        $moveItem->handle($user, $this->item, $newPlace);

        session()->flash('message', __('items.moved', [
            'name' => $this->item->name,
            'place' => $newPlace->name,
        ]));

        $this->redirectRoute('items.show', [$this->household, $this->item], navigate: true);
    }

    public function archive(#[CurrentUser] User $user, ArchiveItemAction $archiveItem): void
    {
        $this->authorize('edit', $this->household);

        $oldPlace = $archiveItem->handle($user, $this->item);

        session()->flash('message', __('items.archived', ['name' => $this->item->name]));
        session()->flash('archived_item_id', $this->item->id);

        $this->redirectRoute('places.show', [$this->household, $oldPlace], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.items.show')
            ->title($this->item->name);
    }

    private function isAlreadyIn(Place $place): bool
    {
        return $this->item->place->is($place);
    }
}
