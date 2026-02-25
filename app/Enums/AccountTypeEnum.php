<?php

namespace App\Enums;

enum AccountTypeEnum: string
{
    case INDIVIDUAL = 'individual';
    case SCHOOL = 'school';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
