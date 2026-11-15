<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Household>
 */
final class HouseholdFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->lastName(),
            'owner_id' => User::factory(),
        ];
    }

    /**
     * Every household has its owner as a member with the role Owner.
     */
    #[Override]
    public function configure(): static
    {
        return $this->afterCreating(function (Household $household): void {
            $household->members()->attach($household->owner_id, ['role' => Role::Owner]);
        });
    }
}
