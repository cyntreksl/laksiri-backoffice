<?php

namespace App\Interfaces;

use App\Models\HBL;

interface CashSettlementInterface
{
    public function getPendingSettlementList() : HBL;

    public function getSummery(array $filters = []) ;
    public function cashReceived(array $hblIds);
}
