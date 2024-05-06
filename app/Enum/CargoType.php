<?php

namespace App\Enum;

enum CargoType: string
{
    case SEA_CARGO = 'Sea Cargo';
    case AIR_CARGO = 'Air Cargo';
    case DOOR_TO_DOOR = 'Door to Door';
}
