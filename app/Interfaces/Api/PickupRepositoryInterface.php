<?php

namespace App\Interfaces\Api;

interface PickupRepositoryInterface
{
    /**
     * Retrieve pending pickups assigned to the authenticated driver.
     *
     * @method  GET api/v1/pending-pickup-list
     */
    public function getPendingPickupsForDriver();

    /**
     * Convert a pickup to HBL.
     *
     * @method  POST api/v1/pickup-to-hbl/{pickUp}
     */
    public function pickupToHbl($pickUp, $request);
}
