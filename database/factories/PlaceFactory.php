<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Household;
use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Place>
 */
final class PlaceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'parent_id' => null,
            'name' => fake()->word(),
        ];
    }

    public function inside(Place $parent): static
    {
        return $this->state(fn (array $attributes): array => [
            'household_id' => $parent->household_id,
            'parent_id' => $parent->id,
        ]);
    }
}
