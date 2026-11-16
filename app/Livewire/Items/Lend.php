<?php

declare(strict_types=1);

namespace App\Livewire\Items;

use App\Actions\LendItemAction;
use App\Livewire\Concerns\ShowsHouseholdName;
use App\Models\Borrower;
use App\Models\Household;
use App\Models\Item;
use App\Models\Place;
use App\Models\User;
use App\Support\PlaceTree;
use Carbon\CarbonImmutable;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read string $householdName
 * @property-read SupportCollection<int, Place> $path
 * @property-read Collection<int, Borrower> $matchingBorrowers
 */
final class Lend extends Component
{
    use ShowsHouseholdName;

    public Household $household;

    public Item $item;

    public string $borrowerName = '';

    /**
     * The dates are strings in the Y-m-d format of the date inputs.
     */
    public string $lentAt = '';

    public ?string $dueAt = null;

    public ?string $note = null;

    public function mount(): void
    {
        $this->authorize('edit', $this->household);

        if ($this->item->isLent()) {
            $this->redirectRoute('items.show', [$this->household, $this->item], navigate: true);

            return;
        }

        $this->lentAt = today()->toDateString();
    }

    /**
     * @return SupportCollection<int, Place>
     */
    #[Computed]
    public function path(): SupportCollection
    {
        return PlaceTree::for($this->household)->placesDownTo($this->item->place);
    }

    /**
     * @return Collection<int, Borrower>
     */
    #[Computed]
    public function matchingBorrowers(): Collection
    {
        $typedName = Str::trim($this->borrowerName);

        if ($typedName === '') {
            return new Collection;
        }

        return $this->household->borrowers()
            ->whereLike('name', sprintf('%%%s%%', $typedName))
            ->orderBy('name')
            ->get();
    }

    /**
     * A typed name that matches no saved borrower is saved as a new one when lending.
     */
    public function isNewBorrower(): bool
    {
        return Str::trim($this->borrowerName) !== ''
            && $this->matchingBorrowers->isEmpty();
    }

    public function lend(#[CurrentUser] User $user, LendItemAction $lendItem): void
    {
        $this->authorize('edit', $this->household);

        $this->validateLoan();

        $loan = $lendItem->handle(
            $user,
            $this->item,
            $this->borrowerName,
            CarbonImmutable::parse($this->lentAt),
            $this->dueAt === null ? null : CarbonImmutable::parse($this->dueAt),
            $this->note,
        );

        session()->flash('message', __('loans.lent', [
            'name' => $this->item->name,
            'borrower' => $loan->borrower->name,
        ]));

        $this->redirectRoute('items.show', [$this->household, $this->item], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.items.lend')
            ->title(__('loans.lend_item', ['name' => $this->item->name]));
    }

    /**
     * Empty optional fields are saved as null.
     */
    private function validateLoan(): void
    {
        $this->borrowerName = Str::trim($this->borrowerName);
        $this->dueAt = $this->dueAt ?: null;
        $this->note = Str::trim((string) $this->note) ?: null;

        $this->validate(
            rules: [
                'borrowerName' => ['required', 'string', 'max:255'],
                'lentAt' => ['required', 'date', 'before_or_equal:today'],
                'dueAt' => ['nullable', 'date', 'after_or_equal:lentAt'],
                'note' => ['nullable', 'string', 'max:255'],
            ],
            messages: [
                'borrowerName.required' => __('loans.borrower_required'),
                'lentAt.required' => __('loans.lent_on_required'),
                'lentAt.before_or_equal' => __('loans.lent_on_in_the_future'),
                'dueAt.after_or_equal' => __('loans.due_before_lent_on'),
            ],
        );
    }
}
