<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Place;
use App\Support\PlaceTree;
use Closure;
use Illuminate\Support\Str;

trait ValidatesPlaceName
{
    /**
     * $except is the place being renamed, so it can keep its own name.
     */
    private function validatePlaceName(?Place $parent, ?Place $except = null): void
    {
        $this->name = Str::trim($this->name);

        $this->validate(
            rules: [
                'name' => [
                    'bail',
                    'required',
                    'string',
                    'max:255',
                    $this->siblingNameIsFreeRule($parent, $except),
                ],
            ],
            messages: [
                'name.required' => __('places.name_required'),
            ],
        );
    }

    private function siblingNameIsFreeRule(?Place $parent, ?Place $except): Closure
    {
        return function (string $attribute, string $value, Closure $fail) use ($parent, $except): void {
            $isTaken = PlaceTree::for($this->household)->hasSiblingNamed($parent, $value, $except);

            if ($isTaken) {
                $fail('places.name_taken')->translate(['name' => $value]);
            }
        };
    }
}
