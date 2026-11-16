<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityType;
use App\Models\Borrower;
use App\Models\Household;
use App\Models\Item;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class LendItemAction
{
    public function __construct(private RecordActivityAction $recordActivity) {}

    public function handle(User $user, Item $item, string $borrowerName, CarbonImmutable $lentAt, ?CarbonImmutable $dueAt, ?string $note): Loan
    {
        return DB::transaction(function () use ($user, $item, $borrowerName, $lentAt, $dueAt, $note): Loan {
            $this->ensureItemIsNotLent($item);

            $borrower = $this->findOrCreateBorrower($item->household, $borrowerName);

            $loan = $borrower->loans()->create([
                'item_id' => $item->id,
                'lent_at' => $lentAt,
                'due_at' => $dueAt,
                'note' => $note,
            ]);

            $this->recordActivity->handle($user, ActivityType::ItemLent, $item, to: $borrower->name);

            return $loan;
        });
    }

    /**
     * An item has at most one open loan (data model, extra rule 5).
     */
    private function ensureItemIsNotLent(Item $item): void
    {
        if ($item->isLent()) {
            throw ValidationException::withMessages([
                'borrowerName' => __('loans.already_lent', ['name' => $item->name]),
            ]);
        }
    }

    /**
     * Names ignore case (extra rule 4). The check runs in PHP, so it is the same on SQLite and PostgreSQL.
     */
    private function findOrCreateBorrower(Household $household, string $name): Borrower
    {
        $savedBorrower = $household->borrowers()
            ->get()
            ->first(fn (Borrower $borrower): bool => Str::lower($borrower->name) === Str::lower($name));

        return $savedBorrower ?? $household->borrowers()->create(['name' => $name]);
    }
}
