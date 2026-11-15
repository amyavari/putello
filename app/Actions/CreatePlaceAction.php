<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityType;
use App\Models\Household;
use App\Models\Place;
use App\Models\User;
use App\Support\PlaceTree;
use Illuminate\Support\Facades\DB;

final readonly class CreatePlaceAction
{
    public function __construct(private RecordActivityAction $recordActivity) {}

    public function handle(User $user, Household $household, ?Place $parent, string $name): Place
    {
        return DB::transaction(function () use ($user, $household, $parent, $name): Place {
            $place = $household->places()->create([
                'parent_id' => $parent?->id,
                'name' => $name,
            ]);

            $path = PlaceTree::for($household)->pathOf($place);

            $this->recordActivity->handle($user, ActivityType::PlaceCreated, $place, to: $path);

            return $place;
        });
    }
}
