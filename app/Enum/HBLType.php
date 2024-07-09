<?php

namespace App\Enum;

enum HBLType: string
{
    case UBP = 'UBP';

    case GIFT = 'Gift';

    case DOOR_TO_DOOR = 'Door to Door';

    public static function getHBLTypeOptions(): array
    {
        return array_filter(self::cases(), fn ($case) => in_array($case, [
            self::UBP,
            self::GIFT,
            self::DOOR_TO_DOOR,
        ]));
    }
}
