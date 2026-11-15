<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Household;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class HouseholdPolicy
{
    /**
     * A user who is not a member gets "not found", so the household's existence stays hidden.
     */
    public function view(User $user, Household $household): Response
    {
        return $household->hasMember($user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Covers changing the household's places and items.
     */
    public function edit(User $user, Household $household): bool
    {
        return $household->canBeEditedBy($user);
    }
}
