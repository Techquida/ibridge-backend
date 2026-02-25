<?php

namespace App\Enums;

enum RoleEnum: string
{
    case STUDENT = 'student';
    case PARTNER = 'partner';
    case SCHOOL_ADMIN = 'school_admin';
    case SYSTEM_ADMIN = 'system_admin';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
