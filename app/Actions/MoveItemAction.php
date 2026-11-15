<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityType;
use App\Models\Item;
use App\Models\Place;
use App\Models\User;
use App\Support\PlaceTree;
use Illuminate\Support\Facades\DB;

final readonly class MoveItemAction
{
    public function __construct(private RecordActivityAction $recordActivity) {}

    public function handle(User $user, Item $item, Place $place): void
    {
        DB::transaction(function () use ($user, $item, $place): void {
            $placeTree = PlaceTree::for($item->household);
            $oldPath = $placeTree->pathOf($item->place);
            $newPath = $placeTree->pathOf($place);

            $item->update([
                'place_id' => $place->id,
            ]);

            $this->recordActivity->handle($user, ActivityType::ItemMoved, $item, from: $oldPath, to: $newPath);
        });
    }
}
