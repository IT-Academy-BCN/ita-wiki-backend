<?php

declare(strict_types=1);

namespace App\Enums;

enum DifficultyLevelEnum: string
{
    case Easy = 'easy';
    case Medium = 'medium';
    case Hard = 'hard';
    case Expert = 'expert';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
