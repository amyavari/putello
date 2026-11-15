<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Place;

final readonly class RenamePlaceAction
{
    /**
     * Give the place a new name. The items and places inside stay where they are.
     */
    public function handle(Place $place, string $name): void
    {
        $place->update([
            'name' => $name,
        ]);
    }
}
