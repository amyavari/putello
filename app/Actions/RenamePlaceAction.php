<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityType;
use App\Models\Place;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class RenamePlaceAction
{
    public function __construct(private RecordActivityAction $recordActivity) {}

    public function handle(User $user, Place $place, string $name): void
    {
        DB::transaction(function () use ($user, $place, $name): void {
            $oldName = $place->name;

            $place->update([
                'name' => $name,
            ]);

            $this->recordActivity->handle($user, ActivityType::PlaceRenamed, $place, from: $oldName, to: $name);
        });
    }
}
