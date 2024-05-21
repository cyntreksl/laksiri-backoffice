<?php

namespace App\Enum;

enum HBLPaymentStatus: string
{
    case FULL_PAID = 'Full Paid';
    case PARTIAL_PAID = 'Partial Paid';
    case NOT_PAID = 'Not Paid';
}
