<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\StorePickupExceptionRequest;
use App\Http\Requests\StorePickupRequest;
use App\Http\Requests\StorePickupToHBLRequest;
use App\Interfaces\Api\PickupRepositoryInterface;
use App\Models\PickUp;
use Illuminate\Http\Request;

class PickupController extends Controller
{
    public function __construct(
        private readonly PickupRepositoryInterface $pickupRepository,
    ) {
    }

    /**
     * Get pending pickups for driver
     *
     * Display the pending pickups for the authenticated driver.
     *
     * @group Pickups
     */
    public function index(Request $request)
    {
        return $this->pickupRepository
            ->getPendingPickupsForDriver($request->only('start_date', 'end_date'));
    }

    /**
     * Store Pickup
     *
     * Store a newly created Pickup in storage.
     *
     * @group Pickups
     */
    public function store(StorePickupRequest $request)
    {
        return $this->pickupRepository->storePickup($request->all());
    }

    /**
     * Show Pickup
     *
     * Display the specified Pickup.
     *
     * @group Pickups
     */
    public function show(PickUp $pickup)
    {
        return $this->pickupRepository->showPickup($pickup);
    }

    /**
     * Pickup to HBL
     *
     * Convert a Pickup to HBL.
     *
     * @group Pickups
     */
    public function pickupToHbl(PickUp $pickUp, StorePickupToHBLRequest $request)
    {
        return $this->pickupRepository->pickupToHbl($pickUp, $request);
    }

    /**
     * Store Pickup Exception
     *
     * Store a newly created Pickup Exception in storage.
     *
     * @group Pickups
     */
    public function storePickupException(StorePickupExceptionRequest $request, PickUp $pickup)
    {
        return $this->pickupRepository->savePickupException($request->all(), $pickup);
    }
    /**
     * Show Pickup Exception
     *
     * Display the specified Pickup Exception.
     *
     * @group Pickups
     */
    public function showPickupException(int $exceptionId)
    {
        return $this->pickupRepository->showPickupException($exceptionId);
    }


    /**
     * Completed Pickup with HBL
     *
     * Display the list of completed pickups with HBL.
     *
     * @group Pickups
     */
    public function completedPickupWithHBL(Request $request)
    {
        return $this->pickupRepository->completedPickupWithHBL($request->only('start_date', 'end_date'));
    }

    /**
     * Get pickup exceptions for driver
     *
     * Display the pickup exceptions for the authenticated driver.
     *
     * @group Pickups
     */
    public function getPickupExceptions(Request $request)
    {
        return $this->pickupRepository
            ->getPickupExceptionsForDriver($request->only('start_date', 'end_date'));
    }
}
