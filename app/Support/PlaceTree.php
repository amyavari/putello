<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Household;
use App\Models\Place;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The places of one household, loaded with one query and arranged as a tree in memory.
 * The number of items in each place is loaded with one more query.
 */
final readonly class PlaceTree
{
    /**
     * The key of the top places in $childrenByParentId, because they have no parent id.
     */
    private const string TOP = '';

    /**
     * @param  Collection<array-key, Place>  $placesById
     * @param  Collection<array-key, Collection<int, Place>>  $childrenByParentId
     * @param  Collection<array-key, int>  $numberOfItemsByPlaceId  Only the items directly in each place.
     */
    private function __construct(
        private Collection $placesById,
        private Collection $childrenByParentId,
        private Collection $numberOfItemsByPlaceId,
    ) {}

    public static function for(Household $household): self
    {
        $places = $household->places()
            ->get()
            ->toBase();

        return new self(
            $places->keyBy('id'),
            self::groupChildrenByParentId($places),
            self::countItemsByPlaceId($household),
        );
    }

    /**
     * @return Collection<int, Place>
     */
    public function topPlaces(): Collection
    {
        return $this->childrenByParentId->get(self::TOP, new Collection);
    }

    /**
     * @return Collection<int, Place>
     */
    public function placesInside(Place $place): Collection
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
     * A place can't go inside itself or a place inside it, and must change where it is.
     * $newParent null is the top of the household.
     */
    public function canBeMovedTo(Place $place, ?Place $newParent): bool
    {
        if (! $newParent instanceof Place) {
            return ! $this->isAtTheTop($place);
        }

        return ! $newParent->is($place)
            && ! $this->isInside($newParent, $place)
            && ! $newParent->is($this->parentOf($place));
    }

    /**
     * The check runs in PHP, so it is the same on SQLite and PostgreSQL.
     *
     * @return Collection<int, Place>
     */
    public function placesNamedLike(string $search): Collection
    {
        return $this->placesById
            ->filter(fn (Place $place): bool => Str::contains($place->name, $search, ignoreCase: true))
            ->sortBy(fn (Place $place): string => Str::lower($place->name))
            ->values();
    }

    /**
     * @return Collection<int, Place>
     */
    public function placesAbove(Place $place): Collection
    {
        $placesAbove = new Collection;
        $parent = $this->parentOf($place);

        while ($parent instanceof Place) {
            $placesAbove->prepend($parent);
            $parent = $this->parentOf($parent);
        }

        return $placesAbove;
    }

    /**
     * The places from the top down to the place, the place included.
     *
     * @return Collection<int, Place>
     */
    public function placesDownTo(Place $place): Collection
    {
        return $this->placesAbove($place)->push($place);
    }

    /**
     * The place and every place inside it, at any depth.
     *
     * @return Collection<int, Place>
     */
    public function placesDownFrom(Place $place): Collection
    {
        $placesInside = $this->placesInside($place)
            ->flatMap(fn (Place $placeInside): Collection => $this->placesDownFrom($placeInside));

        return $placesInside->prepend($place);
    }

    /**
     * Get the names from the top place down to the place, for example "Kitchen › Left cupboard".
     */
    public function pathOf(Place $place): string
    {
        $names = $this->placesDownTo($place)->pluck('name');

        return $names->implode(__('places.path_separator'));
    }

    /**
     * Counts the items in the place and in every place inside it, at any depth.
     */
    public function numberOfItemsInside(Place $place): int
    {
        $numberOfItemsInPlacesInside = $this->placesInside($place)
            ->sum(fn (Place $placeInside): int => $this->numberOfItemsInside($placeInside));

        return $this->numberOfItemsDirectlyIn($place) + $numberOfItemsInPlacesInside;
    }

    public function numberOfAllItems(): int
    {
        return $this->numberOfItemsByPlaceId->sum();
    }

    /**
     * Siblings are sorted by name, ignoring case.
     *
     * @param  Collection<int, Place>  $places
     * @return Collection<array-key, Collection<int, Place>>
     */
    private static function groupChildrenByParentId(Collection $places): Collection
    {
        return $places
            ->sortBy(fn (Place $place): string => Str::lower($place->name))
            ->groupBy(fn (Place $place): string => $place->parent_id ?? self::TOP)
            ->map(fn (Collection $children): Collection => $children->values());
    }

    /**
     * @return Collection<array-key, int>
     */
    private static function countItemsByPlaceId(Household $household): Collection
    {
        return $household->items()
            ->toBase()
            ->selectRaw('place_id, count(*) as number_of_items')
            ->groupBy('place_id')
            ->pluck('number_of_items', 'place_id')
            ->map(fn (mixed $numberOfItems): int => (int) $numberOfItems);
    }

    private function numberOfItemsDirectlyIn(Place $place): int
    {
        return $this->numberOfItemsByPlaceId->get($place->id, 0);
    }

    private function isInside(Place $place, Place $other): bool
    {
        return $this->placesAbove($place)
            ->contains(fn (Place $placeAbove): bool => $placeAbove->is($other));
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
            ? $this->placesInside($parent)
            : $this->topPlaces();
    }

    private function isSameName(string $name, string $otherName): bool
    {
        return Str::lower($name) === Str::lower($otherName);
    }
}
