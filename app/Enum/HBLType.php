<?php

namespace App\Enum;

enum HBLType: string
{
    case UPB = 'UPB';

    case GIFT = 'Gift';

    case DOOR_TO_DOOR = 'Door to Door';

    public static function getHBLTypeOptions(): array
    {
        return array_filter(self::cases(), fn ($case) => in_array($case, [
            self::UPB,
            self::GIFT,
            self::DOOR_TO_DOOR,
        ]));
    }
}
