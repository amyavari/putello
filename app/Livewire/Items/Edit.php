<?php

declare(strict_types=1);

namespace App\Livewire\Items;

use App\Actions\EditItemAction;
use App\Livewire\Concerns\ShowsHouseholdName;
use App\Livewire\Concerns\ValidatesItem;
use App\Models\Household;
use App\Models\Item;
use App\Models\Place;
use App\Models\User;
use App\Support\PlaceTree;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read string $householdName
 * @property-read Collection<int, Place> $path
 */
final class Edit extends Component
{
    use ShowsHouseholdName, ValidatesItem;

    public Household $household;

    public Item $item;

    public function mount(): void
    {
        $this->authorize('edit', $this->household);

        $this->name = $this->item->name;
        $this->notes = $this->item->notes;
    }

    /**
     * @return Collection<int, Place>
     */
    #[Computed]
    public function path(): Collection
    {
        return PlaceTree::for($this->household)->placesDownTo($this->item->place);
    }

    public function save(#[CurrentUser] User $user, EditItemAction $editItem): void
    {
        $this->authorize('edit', $this->household);

        $this->validateItem();

        $editItem->handle($user, $this->item, $this->name, $this->notes);

        session()->flash('message', __('items.saved', ['name' => $this->item->name]));

        $this->redirectRoute('items.show', [$this->household, $this->item], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.items.edit')
            ->title(__('items.edit_item'));
    }
}
