<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityType;
use App\Models\Item;
use App\Models\Place;
use App\Models\User;
use App\Support\PlaceTree;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ArchiveItemAction
{
    public function __construct(private RecordActivityAction $recordActivity) {}

    /**
     * Returns the place the item was in, so the item can be put back there.
     */
    public function handle(User $user, Item $item): Place
    {
        return DB::transaction(function () use ($user, $item): Place {
            $this->ensureItemIsNotLent($item);

            $oldPlace = $item->place;
            $oldPath = PlaceTree::for($item->household)->pathOf($oldPlace);

            $item->update([
                'place_id' => null,
            ]);

            $item->delete();

            $this->recordActivity->handle($user, ActivityType::ItemArchived, $item, from: $oldPath);

            return $oldPlace;
        });
    }

    private function ensureItemIsNotLent(Item $item): void
    {
        if ($item->isLent()) {
            throw ValidationException::withMessages([
                'item' => __('loans.close_loan_first'),
            ]);
        }
    }
}
