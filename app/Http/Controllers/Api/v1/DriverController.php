<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDriverLocation;
use App\Http\Requests\UpdateDriverApiRequest;
use App\Http\Requests\UpdateDriverPasswordRequest;
use App\Interfaces\Api\DriverRepositoryInterface;
use App\Models\User;

class DriverController extends Controller
{
    public function __construct(
        private readonly DriverRepositoryInterface $driverRepository,
    ) {}

    /**
     * Update Driver
     *
     * Update the authenticated driver.
     *
     * @group Driver
     */
    public function store(UpdateDriverApiRequest $request)
    {
        return $this->driverRepository->updateDriver($request);
    }

    /**
     * Track Driver
     *
     * Update the driver location periodically.
     *
     * @group DriverLocation
     */
    public function createDriverLocation(StoreDriverLocation $request, User $user)
    {
        $this->driverRepository->createDriverLocation($user, $request->all());
    }

    /**
     * Get Driver
     *
     * Update the driver Password.
     *
     * @group Driver
     */
    public function updatePassword(UpdateDriverPasswordRequest $request)
    {
        try {
            $updated = $this->driverRepository->updatePassword($request->all());

            if ($updated) {
                return response()->json(['message' => 'Password updated successfully.'], 200);
            }

            return response()->json(['message' => 'Current password is invalid.'], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
