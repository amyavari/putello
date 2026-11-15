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
     * @param  Collection<array-key, Place>  $placesById
     * @param  Collection<array-key, Collection<int, Place>>  $childrenByParentId  Top places are under the key "".
     */
    private function __construct(
        private Collection $placesById,
        private Collection $childrenByParentId,
    ) {}

    public static function for(Household $household): self
    {
        $places = $household->places()
            ->get()
            ->toBase();

        $placesById = $places->keyBy('id');

        $childrenByParentId = $places
            ->sortBy(fn (Place $place): string => Str::lower($place->name))
            ->groupBy(fn (Place $place): string => $place->parent_id ?? '')
            ->map(fn (Collection $children): Collection => $children->values());

        return new self($placesById, $childrenByParentId);
    }

    /**
     * @return Collection<int, Place>
     */
    public function roots(): Collection
    {
        return $this->childrenByParentId->get('', new Collection);
    }

    /**
     * @return Collection<int, Place>
     */
    public function childrenOf(Place $place): Collection
    {
        return $this->childrenByParentId->get($place->id, new Collection);
    }

    public function parentOf(Place $place): ?Place
    {
        if ($this->isAtTheTop($place)) {
            return null;
        }

        return $this->placesById->get($place->parent_id);
    }

    /**
     * The check runs in PHP, so it is the same on SQLite and PostgreSQL.
     */
    public function hasSiblingNamed(?Place $parent, string $name, ?Place $except = null): bool
    {
        return $this->placesDirectlyIn($parent)
            ->reject(fn (Place $place): bool => $place->is($except))
            ->contains(fn (Place $place): bool => $this->isSameName($place->name, $name));
    }

    /**
     * @return Collection<int, Place>
     */
    public function ancestorsOf(Place $place): Collection
    {
        $ancestors = new Collection;
        $parentId = $place->parent_id;

        while ($parentId !== null) {
            $parent = $this->placesById[$parentId];
            $ancestors->prepend($parent);
            $parentId = $parent->parent_id;
        }

        return $ancestors;
    }

    /**
     * Get the names from the top place down to the place, for example "Kitchen › Left cupboard".
     */
    public function pathOf(Place $place): string
    {
        $names = $this->ancestorsOf($place)
            ->push($place)
            ->pluck('name');

        return $names->implode(__('places.path_separator'));
    }

    private function isAtTheTop(Place $place): bool
    {
        return $place->parent_id === null;
    }

    /**
     * @return Collection<int, Place>
     */
    private function placesDirectlyIn(?Place $parent): Collection
    {
        return $parent instanceof Place
            ? $this->childrenOf($parent)
            : $this->roots();
    }

    private function isSameName(string $name, string $otherName): bool
    {
        return Str::lower($name) === Str::lower($otherName);
    }
}
