<?php

declare(strict_types=1);

namespace App\Enums;

enum ContributorStatusEnum: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
