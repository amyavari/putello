<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityType;
use App\Models\Place;
use App\Models\User;
use App\Support\PlaceTree;
use Illuminate\Support\Facades\DB;

final readonly class DeletePlaceAction
{
    public function __construct(private RecordActivityAction $recordActivity) {}

    /**
     * The entry is recorded first; the delete then sets its place link to null.
     * The foreign keys stop the delete if a place or an item is still inside.
     */
    public function handle(User $user, Place $place): void
    {
        DB::transaction(function () use ($user, $place): void {
            $path = PlaceTree::for($place->household)->pathOf($place);

            $this->recordActivity->handle($user, ActivityType::PlaceDeleted, $place, from: $path);

            $place->delete();
        });
    }
}
