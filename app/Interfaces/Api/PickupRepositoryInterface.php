<?php

namespace App\Interfaces\Api;

use App\Models\PickUp;
use Illuminate\Http\JsonResponse;


interface PickupRepositoryInterface
{
    /**
     * Retrieve pending pickups assigned to the authenticated driver.
     *
     * @method  GET api.laksiri.world/v1/pending-pickup-list?start_date={pickup_date}&end_date={pickup_date}
     */
    public function getPendingPickupsForDriver(array $data);

    /**
     * Retrieve details of a pending pickup assigned to the authenticated driver.
     *
     * This method retrieves information about a specific pending pickup assigned to the authenticated driver.
     *
     * @param  PickUp  $pickup  The pending pickup to retrieve details for.
     *
     * @method GET api.laksiri.world/v1/pickups/{id}
     */
    public function showPickup(PickUp $pickup): JsonResponse;

    /**
     * Convert a pickup to HBL.
     *
     * @method  POST api.laksiri.world/v1/pickup-to-hbl/{pickUp}
     */
    public function pickupToHbl($pickUp, $request);

    /**
     * Store pickup details.
     *
     * This method accepts an array of data related to the pickup and processes it to
     * store the necessary information.
     *
     * @param  array  $data  The data related to the pickup to be stored.
     *
     * @method POST api.laksiri.world/v1/pickups
     */
    public function storePickup(array $data): JsonResponse;

    /**
     * Save pickup exception details.
     *
     * This method accepts an array of data related to the pickup exception and a
     * PickUp object, processes the information, and stores the necessary details.
     *
     * @param  array  $data  The data related to the pickup exception to be stored.
     * @param  PickUp  $pickup  The pickup object associated with the exception.
     *
     * @method POST api.laksiri.world/v1/pickups/exceptions/{pickup_id}
     */
    public function savePickupException(array $data, PickUp $pickup): JsonResponse;

    /**
     * Retrieve a list of completed pickups with HBLs.
     *
     * This method retrieves a list of completed pickups along with their corresponding HBLs.
     *
     * @method GET api.laksiri.world/v1/pickups/completed/list?start_date={pickup_date}&end_date={pickup_date}
     */
    public function completedPickupWithHBL(array $data): JsonResponse;

    /**
     * Retrieve pickup exceptions to the authenticated driver.
     *
     * @method  GET api.laksiri.world/v1/pickups/exceptions/list?start_date={pickup_date}&end_date={pickup_date}
     */
    public function getPickupExceptionsForDriver(array $data): JsonResponse;

    /**
     * Retrieve details of a pickup exception to the authenticated driver.
     *
     * @method  GET api.laksiri.world/v1/pickups/exceptions/{id}
     */
    public function showPickupException(int $exceptionId): JsonResponse;


}
