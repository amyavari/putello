<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Override;

/**
 * A user's membership in a household, with their role and their own label for it.
 */
final class HouseholdUser extends Pivot
{
    /**
     * @var bool
     */
    public $incrementing = true;

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'role' => Role::class,
        ];
    }
}
