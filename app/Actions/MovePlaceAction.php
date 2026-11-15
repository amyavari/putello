<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Place;

final readonly class MovePlaceAction
{
    /**
     * $newParent null is the top of the household.
     */
    public function handle(Place $place, ?Place $newParent): void
    {
        $place->update([
            'parent_id' => $newParent?->id,
        ]);
    }
}
