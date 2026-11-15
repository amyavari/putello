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
    case ItemPathChanged = 'item.path_changed';
    case PlaceCreated = 'place.created';
    case PlaceRenamed = 'place.renamed';
    case PlaceMoved = 'place.moved';
    case PlaceDeleted = 'place.deleted';
}
