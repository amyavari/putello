<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityType;
use App\Models\Item;
use App\Models\Place;
use App\Models\User;
use App\Support\PlaceTree;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final readonly class RecordPathChangesAction
{
    public function __construct(private RecordActivityAction $recordActivity) {}

    /**
     * Runs after the place was renamed or moved. Every item path inside the place starts with
     * the place's path, so an item's old path is its new path with that start swapped back.
     */
    public function handle(User $user, Place $place, string $oldPath, string $newPath): void
    {
        $placeTree = PlaceTree::for($place->household);

        foreach ($this->itemsInside($place, $placeTree) as $item) {
            $newItemPath = $placeTree->pathOf($item->place);
            $oldItemPath = Str::replaceStart($newPath, $oldPath, $newItemPath);

            $this->recordActivity->handle($user, ActivityType::ItemPathChanged, $item, from: $oldItemPath, to: $newItemPath);
        }
    }

    /**
     * @return Collection<int, Item>
     */
    private function itemsInside(Place $place, PlaceTree $placeTree): Collection
    {
        $placeIds = $placeTree->placesDownFrom($place)->pluck('id');

        return $place->household
            ->items()
            ->whereIn('place_id', $placeIds)
            ->with(['household', 'place'])
            ->get();
    }
}
