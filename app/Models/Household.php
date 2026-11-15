<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use Database\Factories\HouseholdFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Household extends Model
{
    /** @use HasFactory<HouseholdFactory> */
    use HasFactory, HasUuids;

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The owner is a member too.
     *
     * @return BelongsToMany<User, $this, HouseholdUser, 'membership'>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(HouseholdUser::class)
            ->as('membership')
            ->withPivot('role', 'label')
            ->withTimestamps();
    }

    /**
     * All the places, at any depth, not only the top ones.
     *
     * @return HasMany<Place, $this>
     */
    public function places(): HasMany
    {
        return $this->hasMany(Place::class);
    }

    public function hasMember(User $user): bool
    {
        return $this->members()
            ->whereKey($user->id)
            ->exists();
    }

    public function canBeEditedBy(User $user): bool
    {
        return $this->members()
            ->whereKey($user->id)
            ->wherePivotIn('role', [Role::Owner, Role::Editor])
            ->exists();
    }

    public function nameFor(User $user): string
    {
        $membership = $user->households()
            ->findOrFail($this->id)
            ->membership;

        return $membership->label ?? $this->name;
    }
}
