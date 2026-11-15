<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Household;
use App\Models\Place;

final readonly class CreatePlaceAction
{
    /**
     * Create a place in the household, at the top or inside the parent place.
     */
    public function handle(Household $household, ?Place $parent, string $name): Place
    {
        return $household->places()->create([
            'parent_id' => $parent?->id,
            'name' => $name,
        ]);
    }
}
