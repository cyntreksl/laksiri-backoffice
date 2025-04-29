<?php

namespace App\Interfaces\Finance;

use App\Models\SpecialDOCharge;

interface SpecialDOChargeRepositoryInterface
{
    public function getDOCharges();

    public function storeSpecialDOCharge(array $data);

    public function deleteSpecialDOCharge(SpecialDOCharge $specialDOCharge);
}
