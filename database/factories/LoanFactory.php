<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Borrower;
use App\Models\Item;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * An open loan, to a borrower of the item's household.
 *
 * @extends Factory<Loan>
 */
final class LoanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'borrower_id' => fn (array $attributes): string => Borrower::factory()
                ->create(['household_id' => Item::query()->findOrFail($attributes['item_id'])->household_id])
                ->id,
            'lent_at' => today(),
            'due_at' => null,
            'note' => null,
            'closed_at' => null,
        ];
    }
}
