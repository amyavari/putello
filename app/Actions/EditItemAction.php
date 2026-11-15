<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityType;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class EditItemAction
{
    public function __construct(private RecordActivityAction $recordActivity) {}

    public function handle(User $user, Item $item, string $name, ?string $notes): void
    {
        DB::transaction(function () use ($user, $item, $name, $notes): void {
            $oldName = $item->name;

            $item->update([
                'name' => $name,
                'notes' => $notes,
            ]);

            $this->recordActivity->handle($user, ActivityType::ItemEdited, $item, from: $oldName, to: $name);
        });
    }
}
