<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityType;
use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @return BelongsTo<Household, $this>
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /**
     * @return BelongsTo<Place, $this>
     */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /**
     * The open loan, or none while the item is not lent.
     *
     * @return HasOne<Loan, $this>
     */
    public function currentLoan(): HasOne
    {
        return $this->hasOne(Loan::class)->whereNull('closed_at');
    }

    public function isLent(): bool
    {
        return $this->currentLoan()->exists();
    }

    /**
     * An ordered hasOne, not ofMany(): ofMany() adds MAX(id), and PostgreSQL may not have MAX() for UUIDs.
     * Eager loading keeps the first row for each item, so the latest entry wins.
     *
     * @return HasOne<Activity, $this>
     */
    public function lastArchiveEntry(): HasOne
    {
        return $this->hasOne(Activity::class)
            ->where('type', ActivityType::ItemArchived)
            ->latest('created_at');
    }
}
