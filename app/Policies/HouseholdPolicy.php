<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\Household;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class HouseholdPolicy
{
    /**
     * Determine whether the user can open the household's pages.
     *
     * A user who is not a member gets "not found", so the household's existence stays hidden.
     */
    public function view(User $user, Household $household): Response
    {
        $isMember = $household->members()
            ->whereKey($user->id)
            ->exists();

        return $isMember
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can change the household's places and items.
     */
    public function edit(User $user, Household $household): bool
    {
        return $household->members()
            ->whereKey($user->id)
            ->wherePivotIn('role', [Role::Owner, Role::Editor])
            ->exists();
    }
}
