<?php

namespace App\Interfaces\Api;

use App\Models\PickUp;
use Illuminate\Http\JsonResponse;

interface PickupRepositoryInterface
{
    /**
     * Retrieve pending pickups assigned to the authenticated driver.
     *
     * @method  GET api/v1/pending-pickup-list
     */
    public function getPendingPickupsForDriver();

    /**
     * Retrieve details of a pending pickup assigned to the authenticated driver.
     *
     * This method retrieves information about a specific pending pickup assigned to the authenticated driver.
     *
     * @param  PickUp  $pickup  The pending pickup to retrieve details for.
     *
     * @method GET api/v1/pickups/{id}
     *
     * @return JsonResponse
     */
    public function showPickup(PickUp $pickup): JsonResponse;
}
