<?php

namespace App\Interfaces;

use App\Models\HBL;

interface CashSettlementInterface
{
    public function getPendingSettlementList() : HBL;
}
