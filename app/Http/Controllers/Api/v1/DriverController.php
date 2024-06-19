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

    public function store(UpdateDriverApiRequest $request)
    {
        return $this->DriverRepository->updateDriver($request);
    }
}
