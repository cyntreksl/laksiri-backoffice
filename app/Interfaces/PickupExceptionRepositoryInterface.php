<?php

namespace App\Interfaces;

use App\Models\PickUp;

interface PickupExceptionRepositoryInterface
{
    public function assignDriverToExceptions(array $data);

    public function deleteExceptions(array $exceptionIDs);

    public function export(array $filters);

    public function retryException(PickUp $pickup);
}
