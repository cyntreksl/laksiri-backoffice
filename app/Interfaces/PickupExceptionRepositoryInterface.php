<?php

namespace App\Interfaces;

interface PickupExceptionRepositoryInterface
{
    public function assignDriverToExceptions(array $data);
}
