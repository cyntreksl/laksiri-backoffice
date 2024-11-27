<?php

namespace App\Enum;

enum CountryCode: string
{
    case US = '+1';
    case SRI_LANKA = '+94';
    case SAUDI_ARABIA = '+966';
    case UAE = '+971';
    case KUWAIT = '+965';
    case QATAR = '+974';
    case MALAYSIA = '+60';
    case UK = '+44';

    /**
     * Get all country codes as an array.
     */
    public static function getCountryCodes(): array
    {
        return array_filter(self::cases(), fn ($case) => in_array($case, [
            self::SRI_LANKA,
            self::SAUDI_ARABIA,
            self::UAE,
            self::KUWAIT,
            self::QATAR,
            self::MALAYSIA,
            self::UK,
        ]));
    }
}
