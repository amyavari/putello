<?php

declare(strict_types=1);

namespace App\Livewire\Archived;

use App\Actions\RestoreItemAction;
use App\Livewire\Concerns\ShowsHouseholdName;
use App\Models\Household;
use App\Models\Item;
use App\Models\Place;
use App\Models\User;
use App\Support\ShortDate;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * @property-read string $householdName
 * @property-read Collection<int, Item> $archivedItems
 */
final class Index extends Component
{
    use ShowsHouseholdName;

    public Household $household;

    /**
     * The item whose Restore was pressed, or null before any press.
     */
    #[Locked]
    public ?string $itemToRestoreId = null;

    /**
     * @return Collection<int, Item>
     */
    #[Computed]
    public function archivedItems(): Collection
    {
        return $this->household->items()
            ->onlyTrashed()
            ->with('lastArchiveEntry')
            ->latest('deleted_at')
            ->get();
    }

    public function isEmpty(): bool
    {
        return $this->archivedItems->isEmpty();
    }

    public function archivedOn(Item $item): string
    {
        return ShortDate::of($item->deleted_at);
    }

    /**
     * The path of the place the item was in, or null when no entry was recorded.
     */
    public function wasIn(Item $item): ?string
    {
        return $item->lastArchiveEntry?->from_value;
    }

    public function chooseItemToRestore(string $itemId): void
    {
        $this->itemToRestoreId = $itemId;

        $this->modal('restore-item')->show();
    }

    /**
     * $placeId null is the household, which can't hold items.
     */
    #[On('place-chosen')]
    public function restore(#[CurrentUser] User $user, RestoreItemAction $restoreItem, ?string $placeId): void
    {
        $this->authorize('edit', $this->household);

        $item = $this->household->items()
            ->onlyTrashed()
            ->findOrFail($this->itemToRestoreId);

        $place = $this->household->places()->find($placeId);

        if (! $place instanceof Place) {
            $this->addError('placeId', __('places.cannot_choose_place'));

            return;
        }

        $restoreItem->handle($user, $item, $place);

        session()->flash('message', __('items.restored', [
            'name' => $item->name,
            'place' => $place->name,
        ]));

        $this->redirectRoute('items.show', [$this->household, $item], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.archived.index')
            ->title(__('archived.title'));
    }
}
