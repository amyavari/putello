<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Household;
use App\Models\Place;

trait RedirectsToPlaces
{
    /**
     * Opens the place's page, or the Places page when there is no place.
     */
    private function redirectToPlaces(Household $household, string $message, ?Place $place = null): void
    {
        session()->flash('message', $message);

        if ($place instanceof Place) {
            $this->redirectRoute('places.show', [$household, $place], navigate: true);

            return;
        }

        $this->redirectRoute('places.index', $household, navigate: true);
    }
}
