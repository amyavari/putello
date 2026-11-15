<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * Entries are never edited, so there is no updated_at.
 */
final class Activity extends Model
{
    use HasUuids;

    public const null UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
        ];
    }
}
