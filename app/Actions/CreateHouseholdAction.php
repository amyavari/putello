<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\Role;
use App\Models\Household;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class CreateHouseholdAction
{
    public function handle(User $owner, string $name): Household
    {
        return DB::transaction(function () use ($owner, $name): Household {
            $household = Household::query()->create([
                'name' => $name,
                'owner_id' => $owner->id,
            ]);

            $household->members()->attach($owner, ['role' => Role::Owner]);

            return $household;
        });
    }
}
