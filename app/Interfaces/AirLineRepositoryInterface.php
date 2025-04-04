<?php

namespace App\Interfaces;

use App\Models\AirLine;

interface AirLineRepositoryInterface
{
    public function getAirLines();

    public function createAirLine(array $data);

    public function updateAirLine(AirLine $airLine, array $data);

    public function destroyAirLine(AirLine $airLine);
}
