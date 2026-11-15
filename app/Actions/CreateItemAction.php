<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityType;
use App\Models\Item;
use App\Models\Place;
use App\Models\User;
use App\Support\PlaceTree;
use Illuminate\Support\Facades\DB;

final readonly class CreateItemAction
{
    public function __construct(private RecordActivityAction $recordActivity) {}

    public function handle(User $user, Place $place, string $name, ?string $notes): Item
    {
        return DB::transaction(function () use ($user, $place, $name, $notes): Item {
            $item = $place->items()->create([
                'household_id' => $place->household_id,
                'name' => $name,
                'notes' => $notes,
            ]);

            $placePath = PlaceTree::for($place->household)->pathOf($place);

            $this->recordActivity->handle($user, ActivityType::ItemCreated, $item, to: $placePath);

            return $item;
        });
    }
}
