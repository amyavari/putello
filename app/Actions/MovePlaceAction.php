<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityType;
use App\Models\Place;
use App\Models\User;
use App\Support\PlaceTree;
use Illuminate\Support\Facades\DB;

final readonly class MovePlaceAction
{
    public function __construct(private RecordActivityAction $recordActivity) {}

    /**
     * $newParent null is the top of the household.
     */
    public function handle(User $user, Place $place, ?Place $newParent): void
    {
        DB::transaction(function () use ($user, $place, $newParent): void {
            $oldPath = PlaceTree::for($place->household)->pathOf($place);

            $place->update([
                'parent_id' => $newParent?->id,
            ]);

            $newPath = PlaceTree::for($place->household)->pathOf($place);

            $this->recordActivity->handle($user, ActivityType::PlaceMoved, $place, from: $oldPath, to: $newPath);
        });
    }
}
