<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignDriverRequest;
use App\Interfaces\CallCenter\DeliveryRepositoryInterface;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;

class DeliverController extends Controller
{
    use ResponseAPI;

    public function __construct(
        private readonly DeliveryRepositoryInterface $deliveryRepository,
    ) {
    }

    public function index(Request $request)
    {
        return $this->deliveryRepository->getPendingDeliverForDriver();
    }

    public function assignDriver(AssignDriverRequest $request)
    {
        return $this->deliveryRepository->assignDriverToDeliver($request->all());
    }
}
