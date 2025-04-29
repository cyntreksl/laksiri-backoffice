<?php

namespace App\Interfaces\Finance;

use App\Models\SpecialDOCharge;

interface SpecialDOChargeRepositoryInterface
{
    public function getDOCharges();

    public function storeSpecialDOCharge(array $data);

    public function updateSpecialDOCharge(array $data, SpecialDOCharge $specialDOCharge);

    public function deleteSpecialDOCharge(SpecialDOCharge $specialDOCharge);
}
