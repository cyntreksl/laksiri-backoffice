<?php

namespace App\Enum;

enum PickupType: string
{
    case URGENT_PICKUP = 'Urgent Pickup ';
    case VIP_CUSTOMER = 'VIP Customer';
    case NEED_TROLLY = 'Need Trolly';
}
