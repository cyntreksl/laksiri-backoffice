<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignDriverRequest;
use App\Interfaces\CallCenter\DeliveryRepositoryInterface;

class DeliverController extends Controller
{
    public function __construct(
        private readonly DeliveryRepositoryInterface $deliveryRepository,
    ) {
    }

    public function assignDriver(AssignDriverRequest $request)
    {
        return $this->deliveryRepository->assignDriverToDeliver($request->all());
    }
}
