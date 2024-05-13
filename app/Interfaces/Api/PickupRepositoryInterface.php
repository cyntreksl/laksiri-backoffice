<?php

namespace App\Interfaces\Api;

use Illuminate\Http\JsonResponse;

interface PickupRepositoryInterface
{
    /**
     * Retrieve pending pickups assigned to the authenticated driver.
     *
     * @method  GET api/v1/pending-pickup-list
     *
     */
    public function getPendingPickupsForDriver();
}
