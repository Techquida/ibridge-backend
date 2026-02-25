<?php

namespace App\Enums;

enum SessionModeEnum: string
{
    case LIGHT = 'light';
    case DEEP = 'deep';
    case REAL_EXAM = 'real_exam';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
