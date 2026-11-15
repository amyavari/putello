<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityType;
use App\Models\Item;
use App\Models\Place;
use App\Models\User;

final readonly class RecordActivityAction
{
    /**
     * The names and paths are saved as text, so later renames and deletes never change the entry.
     */
    public function handle(User $user, ActivityType $type, Item|Place $subject, ?string $from = null, ?string $to = null): void
    {
        $subject->household->activities()->create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => $type,
            'item_id' => $subject instanceof Item ? $subject->id : null,
            'place_id' => $subject instanceof Place ? $subject->id : null,
            'subject_name' => $subject->name,
            'from_value' => $from,
            'to_value' => $to,
        ]);
    }
}
