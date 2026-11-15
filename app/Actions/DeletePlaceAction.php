<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Place;

final readonly class DeletePlaceAction
{
    /**
     * The foreign key stops the delete if a place is still inside.
     */
    public function handle(Place $place): void
    {
        $place->delete();
    }
}
