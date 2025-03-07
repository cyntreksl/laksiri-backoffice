<?php

namespace App\Repositories;

use App\Actions\Courier\CreateCourier;
use App\Actions\Courier\CreateCourierPackages;
use App\Interfaces\CourierRepositoryInterface;
use App\Models\Courier;

class CourierRepository implements CourierRepositoryInterface
{
    public function storeCourier(array $data)
    {
        $data['status'] = Courier::PENDING;
        $courier = CreateCourier::run($data);
        $packagesData = $data['packages'];
        CreateCourierPackages::run($courier, $packagesData);
        $courier->addStatus('Courier Created');

        return $courier;

    }
}
