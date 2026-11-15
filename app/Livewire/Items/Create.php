<?php

declare(strict_types=1);

namespace App\Livewire\Items;

use App\Actions\CreateItemAction;
use App\Livewire\Concerns\ShowsHouseholdName;
use App\Models\Household;
use App\Models\Place;
use App\Support\PlaceTree;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read string $householdName
 * @property-read Collection<int, Place> $path
 */
final class Create extends Component
{
    use ShowsHouseholdName;

    public Household $household;

    /**
     * The place the new item goes in.
     */
    public Place $place;

    public string $name = '';

    public ?string $notes = null;

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

        $this->name = Str::trim($this->name);
        $this->notes = Str::trim((string) $this->notes) ?: null;

        $this->validate(
            rules: [
                'name' => ['required', 'string', 'max:255'],
                'notes' => ['nullable', 'string', 'max:255'],
            ],
            messages: [
                'name.required' => __('items.name_required'),
            ],
        );

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
