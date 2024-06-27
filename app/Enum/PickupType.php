<?php

namespace App\Enum;

enum PickupType: string
{
    case URGENT_PICKUP = 'Urgent Pickup ';
    case VIP_CUSTOMER = 'VIP Customer';
    case NEED_TROLLY = 'Need Trolly';

    public static function getCargoTypeOptions(): array
    {
        return array_filter(self::cases(), fn ($case) => in_array($case, [
            self::URGENT_PICKUP,
            self::VIP_CUSTOMER,
            self::NEED_TROLLY,
        ]));
    }
}
