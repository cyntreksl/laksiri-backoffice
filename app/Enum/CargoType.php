<?php

namespace App\Enum;

enum CargoType: string
{
    case SEA_CARGO = 'Sea Cargo';
    case AIR_CARGO = 'Air Cargo';
    case DOOR_TO_DOOR = 'Door to Door';

    public static function getCargoTypeOptions(): array
    {
        return array_filter(self::cases(), fn ($case) => in_array($case, [
            self::SEA_CARGO,
            self::AIR_CARGO,
        ]));
    }
}
