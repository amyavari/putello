<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Household;
use App\Models\Item;
use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
final class ItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'place_id' => fn (array $attributes): string => Place::factory()
                ->create(['household_id' => $attributes['household_id']])
                ->id,
            'name' => fake()->word(),
            'notes' => null,
        ];
    }

    public function in(Place $place): static
    {
        return $this->state(fn (array $attributes): array => [
            'household_id' => $place->household_id,
            'place_id' => $place->id,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'place_id' => null,
            'deleted_at' => now(),
        ]);
    }
}
