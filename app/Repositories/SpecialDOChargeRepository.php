<?php

namespace App\Repositories;

use App\Actions\SpecialDOCharge\CreateSpecialDOCharge;
use App\Interfaces\SpecialDOChargeRepositoryInterface;

class SpecialDOChargeRepository implements SpecialDOChargeRepositoryInterface
{
    public function storeSpecialDOCharge(array $data)
    {
        return CreateSpecialDOCharge::run($data);
    }
}
