<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case Owner = 'owner';
    case Editor = 'editor';
    case Viewer = 'viewer';

    /**
     * Get the name of the role shown to users.
     */
    public function label(): string
    {
        return match ($this) {
            self::Owner => __('households.roles.owner'),
            self::Editor => __('households.roles.editor'),
            self::Viewer => __('households.roles.viewer'),
        };
    }
}
