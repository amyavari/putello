<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Item;
use App\Models\Place;

final readonly class RestoreItemAction
{
    public function handle(Item $item, Place $place): void
    {
        $item->place_id = $place->id;

        $item->restore();
    }
}
