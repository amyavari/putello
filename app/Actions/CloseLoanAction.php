<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityType;
use App\Enums\LoanClosedAs;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class CloseLoanAction
{
    public function __construct(private RecordActivityAction $recordActivity) {}

    public function handle(User $user, Loan $loan, LoanClosedAs $closedAs): void
    {
        DB::transaction(function () use ($user, $loan, $closedAs): void {
            $this->ensureLoanIsOpen($loan);

            $loan->update([
                'closed_at' => now(),
                'closed_as' => $closedAs,
            ]);

            $this->recordActivity->handle($user, ActivityType::ItemReturned, $loan->item, to: $loan->borrower->name);
        });
    }

    /**
     * The loan is read again, because someone may have closed it in another tab.
     */
    private function ensureLoanIsOpen(Loan $loan): void
    {
        $loan->refresh();

        if ($loan->closed_at !== null) {
            throw ValidationException::withMessages([
                'closedAs' => __('loans.already_closed'),
            ]);
        }
    }
}
