<?php

namespace App\Enum;

enum HBLImageType: string
{
    case SHIPPER_NIC = 'shipper_nic';
    case SHIPPER_PASSPORT = 'shipper_passport';
    case PACKAGE = 'package';
}
