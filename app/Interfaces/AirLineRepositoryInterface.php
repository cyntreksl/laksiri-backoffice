<?php

namespace App\Interfaces;

interface AirLineRepositoryInterface
{
    public function getAirLines();

    public function createAirLine(array $data);
}
