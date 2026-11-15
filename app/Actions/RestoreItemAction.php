<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityType;
use App\Models\Item;
use App\Models\Place;
use App\Models\User;
use App\Support\PlaceTree;
use Illuminate\Support\Facades\DB;

final readonly class RestoreItemAction
{
    public function __construct(private RecordActivityAction $recordActivity) {}

    public function handle(User $user, Item $item, Place $place): void
    {
        DB::transaction(function () use ($user, $item, $place): void {
            $item->place_id = $place->id;

            $item->restore();

            $placePath = PlaceTree::for($place->household)->pathOf($place);

            $this->recordActivity->handle($user, ActivityType::ItemRestored, $item, to: $placePath);
        });
    }
}
