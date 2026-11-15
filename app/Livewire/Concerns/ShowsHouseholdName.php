<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

trait ShowsHouseholdName
{
    #[Computed]
    public function householdName(): string
    {
        return $this->household->nameFor(Auth::user());
    }
}
