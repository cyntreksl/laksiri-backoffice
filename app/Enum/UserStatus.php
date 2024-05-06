<?php

namespace App\Enum;

enum UserStatus: string
{
    case ACTIVE = 'ACTIVE';
    case DEACTIVATE = 'DEACTIVATE';
    case DELETED = 'DELETED';
    case INVITED = 'INVITED';
    case INACTIVE = 'INACTIVE';
}
