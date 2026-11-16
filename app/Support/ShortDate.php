<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonInterface;

final class ShortDate
{
    /**
     * "Nov 3", or "Nov 3, 2025" in another year: the year shows only when it is not the current year (UI rules).
     */
    public static function of(CarbonInterface $date): string
    {
        return $date->isCurrentYear()
            ? $date->translatedFormat('M j')
            : $date->translatedFormat('M j, Y');
    }
}
