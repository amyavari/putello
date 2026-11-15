<?php

declare(strict_types=1);

namespace App\Livewire\Items;

use App\Actions\CreateItemAction;
use App\Livewire\Concerns\ShowsHouseholdName;
use App\Livewire\Concerns\ValidatesItem;
use App\Models\Household;
use App\Models\Place;
use App\Support\PlaceTree;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
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
     * The place the new item goes in.
     */
    public Place $place;

    public function mount(Request $request): void
    {
        $this->authorize('edit', $this->household);

        abort_unless($request->filled('inside'), 404);

        $this->place = $this->household->places()->findOrFail(
            $request->string('inside')->toString(),
        );
    }

    /**
     * @return Collection<int, Place>
     */
    #[Computed]
    public function path(): Collection
    {
        return PlaceTree::for($this->household)->placesDownTo($this->place);
    }

    public function add(CreateItemAction $createItem): void
    {
        $this->authorize('edit', $this->household);

        $this->validateItem();

        $item = $createItem->handle($this->place, $this->name, $this->notes);

        session()->flash('message', __('items.added', ['name' => $item->name]));

        $this->redirectRoute('items.show', [$this->household, $item], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.items.create')
            ->title(__('items.add'));
    }
}
