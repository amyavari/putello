<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityType;
use App\Models\Place;
use App\Models\User;
use App\Support\PlaceTree;
use Illuminate\Support\Facades\DB;

final readonly class RenamePlaceAction
{
    public function __construct(
        private RecordActivityAction $recordActivity,
        private RecordPathChangesAction $recordPathChanges,
    ) {}

    public function handle(User $user, Place $place, string $name): void
    {
        DB::transaction(function () use ($user, $place, $name): void {
            $oldName = $place->name;
            $oldPath = PlaceTree::for($place->household)->pathOf($place);

            $place->update([
                'name' => $name,
            ]);

            $newPath = PlaceTree::for($place->household)->pathOf($place);

            $this->recordActivity->handle($user, ActivityType::PlaceRenamed, $place, from: $oldName, to: $name);
            $this->recordPathChanges->handle($user, $place, $oldPath, $newPath);
        });
    }
}
