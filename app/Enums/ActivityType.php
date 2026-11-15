<?php

declare(strict_types=1);

namespace App\Enums;

enum ActivityType: string
{
    case ItemCreated = 'item.created';
    case ItemEdited = 'item.edited';
    case ItemMoved = 'item.moved';
    case ItemArchived = 'item.archived';
    case ItemRestored = 'item.restored';
}
