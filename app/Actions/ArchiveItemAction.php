<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Item;
use App\Models\Place;
use Illuminate\Support\Facades\DB;

final readonly class ArchiveItemAction
{
    /**
     * Returns the place the item was in, so the item can be put back there.
     */
    public function handle(Item $item): Place
    {
        return DB::transaction(function () use ($item): Place {
            $oldPlace = $item->place;

            $item->update([
                'place_id' => null,
            ]);

            $item->delete();

            return $oldPlace;
        });
    }
}
