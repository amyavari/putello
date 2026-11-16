<?php

declare(strict_types=1);

namespace App\Livewire\Loans;

use App\Actions\CloseLoanAction;
use App\Enums\LoanClosedAs;
use App\Models\Loan;
use App\Models\User;
use App\Support\PlaceTree;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

final class Close extends Component
{
    public Loan $loan;

    /**
     * Returned or Lost, or null until one is chosen.
     */
    public ?string $closedAs = null;

    public function close(#[CurrentUser] User $user, CloseLoanAction $closeLoan): void
    {
        $item = $this->loan->item;

        $this->authorize('edit', $item->household);

        $this->validate(
            rules: [
                'closedAs' => ['required', Rule::enum(LoanClosedAs::class)->only(LoanClosedAs::Returned)],
            ],
            messages: [
                'closedAs.required' => __('loans.choose_returned_or_lost'),
            ],
        );

        $closeLoan->handle($user, $this->loan, LoanClosedAs::from((string) $this->closedAs));

        session()->flash('message', __('loans.returned_message', ['name' => $item->name]));

        $this->redirectRoute('items.show', [$item->household, $item], navigate: true);
    }

    public function render(): View
    {
        $item = $this->loan->item;

        return view('livewire.loans.close', [
            'path' => PlaceTree::for($item->household)->pathOf($item->place),
        ]);
    }
}
