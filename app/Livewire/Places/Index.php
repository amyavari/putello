<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Models\Household;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class Index extends Component
{
    public Household $household;

    public ?string $message = null;

    /**
     * Show the message the previous page left, if any.
     */
    public function mount(): void
    {
        $this->message = session('message');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.places.index')
            ->title(__('places.title'));
    }
}
