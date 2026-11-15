<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use Illuminate\Support\Str;

trait ValidatesItem
{
    public string $name = '';

    public ?string $notes = null;

    /**
     * Empty notes are saved as null, so the item page says "No notes".
     */
    private function validateItem(): void
    {
        $this->name = Str::trim($this->name);
        $this->notes = Str::trim((string) $this->notes) ?: null;

        $this->validate(
            rules: [
                'name' => ['required', 'string', 'max:255'],
                'notes' => ['nullable', 'string', 'max:255'],
            ],
            messages: [
                'name.required' => __('items.name_required'),
            ],
        );
    }
}
