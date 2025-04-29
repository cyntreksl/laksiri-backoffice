<?php

namespace App\Repositories\Finance;

use App\Actions\SpecialDOCharge\CreateSpecialDOCharge;
use App\Actions\SpecialDOCharge\DeleteSpecialDOCharge;
use App\Actions\SpecialDOCharge\GetSpecialDOCharges;
use App\Actions\SpecialDOCharge\UpdateDOCharge;
use App\Interfaces\Finance\SpecialDOChargeRepositoryInterface;
use App\Models\SpecialDOCharge;

class SpecialDOChargeRepository implements SpecialDOChargeRepositoryInterface
{
    public function getDOCharges()
    {
        return GetSpecialDOCharges::run();
    }

    public function storeSpecialDOCharge(array $data)
    {
        return CreateSpecialDOCharge::run($data);
    }

    public function updateSpecialDOCharge(array $data, SpecialDOCharge $specialDOCharge)
    {
        return UpdateDOCharge::run($data, $specialDOCharge);
    }

    public function deleteSpecialDOCharge(SpecialDOCharge $specialDOCharge)
    {
        return DeleteSpecialDOCharge::run($specialDOCharge);
    }
}
