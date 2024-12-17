<?php

namespace App\Repositories;

use App\Actions\Officer\CreateShipperOfficer;
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

    public function storeshipperOfficers(array $data)
    {
        try {
            return CreateShipperOfficer::run($data);

        } catch (\Exception $e) {
            throw new \Exception('Failed to create Shipper Officer: '.$e->getMessage());
        }

    }
}
