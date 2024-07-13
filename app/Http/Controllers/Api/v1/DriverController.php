<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDriverApiRequest;
use App\Repositories\Api\DriverRepository;

class DriverController extends Controller
{
    public function __construct(
        private readonly DriverRepository $DriverRepository,
    ) {
    }

    /**
     * Update Driver
     *
     * Update the authenticated driver.
     *
     * @group Driver
     */
    public function store(UpdateDriverApiRequest $request)
    {
        return $this->DriverRepository->updateDriver($request);
    }
}
