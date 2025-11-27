<?php

declare(strict_types=1);

namespace App\Enums;

enum TechnicalTestStatusEnum: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
