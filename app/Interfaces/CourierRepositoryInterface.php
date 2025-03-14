<?php

namespace App\Interfaces;

use App\Models\Courier;

interface CourierRepositoryInterface
{
    public function storeCourier(array $data);

    public function deleteCourier(Courier $courier);
}
