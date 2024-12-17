<?php

namespace App\Repositories;

use App\Actions\Officer\GetOfficers;
use App\Actions\Officer\GetOfficersByType;
use App\Interfaces\OfficerRepositoryInterface;

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

    public function getAllofficers()
    {
        return GetOfficers::run();
    }
}
