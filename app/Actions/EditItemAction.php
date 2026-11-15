<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Item;

final readonly class EditItemAction
{
    public function handle(Item $item, string $name, ?string $notes): void
    {
        $item->update([
            'name' => $name,
            'notes' => $notes,
        ]);
    }
}
