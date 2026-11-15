<?php

declare(strict_types=1);

namespace App\Livewire\Archived;

use App\Livewire\Concerns\ShowsHouseholdName;
use App\Models\Household;
use App\Models\Item;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
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
     * @return Collection<int, Item>
     */
    #[Computed]
    public function archivedItems(): Collection
    {
        return $this->household->items()
            ->onlyTrashed()
            ->latest('deleted_at')
            ->get();
    }

    public function isEmpty(): bool
    {
        return $this->archivedItems->isEmpty();
    }

    /**
     * The year is shown only when it is not the current year (UI rules).
     */
    public function archivedOn(Item $item): string
    {
        $archivedAt = $item->deleted_at;

        return $archivedAt->isCurrentYear()
            ? $archivedAt->translatedFormat('M j')
            : $archivedAt->translatedFormat('M j, Y');
    }

    public function render(): View
    {
        return view('livewire.archived.index')
            ->title(__('archived.title'));
    }
}
