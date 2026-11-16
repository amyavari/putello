<?php

declare(strict_types=1);

namespace App\Livewire\Search;

use App\Models\Household;
use App\Models\Item;
use App\Models\Place;
use App\Support\PlaceTree;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * @property-read PlaceTree $tree
 * @property-read Collection<int, Item> $matchingItems
 * @property-read SupportCollection<int, Place> $matchingPlaces
 */
final class Index extends Component
{
    public Household $household;

    #[Url(as: 'q')]
    public string $search = '';

    #[Computed]
    public function tree(): PlaceTree
    {
        return PlaceTree::for($this->household);
    }

    /**
     * @return Collection<int, Item>
     */
    #[Computed]
    public function matchingItems(): Collection
    {
        if ($this->isSearchEmpty()) {
            return new Collection;
        }

        $pattern = sprintf('%%%s%%', $this->search);

        return $this->household->items()
            ->where(fn (Builder $query): Builder => $query
                ->whereLike('name', $pattern)
                ->orWhereLike('notes', $pattern))
            ->with(['place', 'currentLoan.borrower'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @return SupportCollection<int, Place>
     */
    #[Computed]
    public function matchingPlaces(): SupportCollection
    {
        if ($this->isSearchEmpty()) {
            return new SupportCollection;
        }

        return $this->tree->placesNamedLike($this->search);
    }

    public function isNothingFound(): bool
    {
        return ! $this->isSearchEmpty()
            && $this->matchingItems->isEmpty()
            && $this->matchingPlaces->isEmpty();
    }

    public function render(): View
    {
        return view('livewire.search.index')
            ->title(__('search.title'));
    }

    private function isSearchEmpty(): bool
    {
        return blank($this->search);
    }
}
