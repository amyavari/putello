<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

trait ShowsFlashMessage
{
    public ?string $message = null;

    public function mountShowsFlashMessage(): void
    {
        $this->message = session('message');
    }
}
