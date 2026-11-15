<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Place;

final readonly class RenamePlaceAction
{
    public function handle(Place $place, string $name): void
    {
        $place->update([
            'name' => $name,
        ]);
    }
}
