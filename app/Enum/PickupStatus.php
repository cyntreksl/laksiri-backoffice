<?php

namespace App\Enum;

enum PickupStatus: string
{
    case PENDING = 'PENDING';
    case COLLECTED = 'COLLECTED';
    case ACCEPTED = 'ACCEPTED';
    case REJECTED = 'REJECTED';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

}
