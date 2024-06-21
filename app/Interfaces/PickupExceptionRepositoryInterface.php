<?php

namespace App\Interfaces;

interface PickupExceptionRepositoryInterface
{
    public function assignDriverToExceptions(array $data);

    public function deleteExceptions(array $exceptionIDs);
}
