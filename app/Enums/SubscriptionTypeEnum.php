<?php

namespace App\Enums;

enum SubscriptionTypeEnum: string
{
    case INDIVIDUAL_ACTIVE = 'individual_active';
    case INDIVIDUAL_EXPIRED = 'individual_expired';
    case SCHOOL_STANDARD = 'school_standard';
    case SCHOOL_LEGENDARY = 'school_legendary';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
