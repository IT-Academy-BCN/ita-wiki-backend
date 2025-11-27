<?php

declare(strict_types=1);

namespace App\Enums;

enum LanguageEnum: string
{
    case PHP = 'PHP';
    case JavaScript = 'JavaScript';
    case Java = 'Java';
    case React = 'React';
    case TypeScript = 'TypeScript';
    case Python = 'Python';
    case SQL = 'SQL';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
} 