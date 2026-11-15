<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Household;
use App\Models\Place;

final readonly class CreatePlaceAction
{
    public function handle(Household $household, ?Place $parent, string $name): Place
    {
        return $household->places()->create([
            'parent_id' => $parent?->id,
            'name' => $name,
        ]);
    }
}
