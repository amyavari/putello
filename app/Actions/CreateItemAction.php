<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Item;
use App\Models\Place;

final readonly class CreateItemAction
{
    public function handle(Place $place, string $name, ?string $notes): Item
    {
        return $place->items()->create([
            'household_id' => $place->household_id,
            'name' => $name,
            'notes' => $notes,
        ]);
    }
}
