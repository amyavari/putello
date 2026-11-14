<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Household;
use App\Models\Place;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The places of one household, loaded with one query and arranged as a tree in memory.
 */
final readonly class PlaceTree
{
    /**
     * @param  Collection<array-key, Collection<int, Place>>  $childrenByParentId  Top places are under the key "".
     */
    private function __construct(
        private Collection $childrenByParentId,
    ) {}

    /**
     * Load the places of the household, with siblings sorted by name, ignoring case.
     */
    public static function for(Household $household): self
    {
        $childrenByParentId = $household->places()
            ->get()
            ->toBase()
            ->sortBy(fn (Place $place): string => Str::lower($place->name))
            ->groupBy(fn (Place $place): string => $place->parent_id ?? '')
            ->map(fn (Collection $children): Collection => $children->values());

        return new self($childrenByParentId);
    }

    /**
     * Get the places that sit directly in the household.
     *
     * @return Collection<int, Place>
     */
    public function roots(): Collection
    {
        return $this->childrenByParentId->get('', new Collection);
    }

    /**
     * Get the places directly inside the place.
     *
     * @return Collection<int, Place>
     */
    public function childrenOf(Place $place): Collection
    {
        return $this->childrenByParentId->get($place->id, new Collection);
    }
}
