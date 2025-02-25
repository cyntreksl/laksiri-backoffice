<?php

namespace App\Repositories;

use App\Actions\Courier\CreateCourier;
use App\Interfaces\CourierRepositoryInterface;
use App\Interfaces\GridJsInterface;

class CourierRepository implements CourierRepositoryInterface
{
    public function  storeCourier(array $data)
    {
        $courier = CreateCourier::run($data);
        $packagesData = $data['packages'];
        CreateCourier::run ($courier, $packagesData);
        $courier->addStatus('Courier Created');

        return $courier;

    }


}
