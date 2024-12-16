<?php

namespace App\Repositories;

use App\Actions\Officer\GetOfficersByType;
use App\Interfaces\OfficerRepositoryInterface;
use App\Models\Officer;

class OfficerRepository implements OfficerRepositoryInterface
{
    public function getShippers()
    {
        return GetOfficersByType::run('shipper');

    }

    public function getConsignees()
    {
        return GetOfficersByType::run('consignee');
    }
}
