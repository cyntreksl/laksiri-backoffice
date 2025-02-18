<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignDriverRequest;
use App\Http\Requests\ReleaseDeliveryRequest;
use App\Interfaces\CallCenter\DeliveryRepositoryInterface;
use App\Interfaces\DriverRepositoryInterface;
use App\Models\HBLDeliver;
use App\Traits\ResponseAPI;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeliverController extends Controller
{
    use AuthorizesRequests;
    use ResponseAPI;

    public function __construct(
        private readonly DeliveryRepositoryInterface $deliveryRepository,
        private readonly DriverRepositoryInterface $driverRepository,
    ) {}

    public function index(Request $request)
    {
        return $this->deliveryRepository->getPendingDeliverForDriver();
    }

    public function assignDriver(AssignDriverRequest $request)
    {
        return $this->deliveryRepository->assignDriverToDeliver($request->all());
    }

    public function show(HBLDeliver $hblDeliver)
    {
        return $this->deliveryRepository->showDeliver($hblDeliver);
    }

    public function showDeliverOrder(Request $request)
    {
        $this->authorize('delivers.show deliver order');

        return Inertia::render('CallCenter/Delivery/DeliveryOrder', [
            'filters' => $request->only('fromDate', 'toDate', 'driverId'),
            'drivers' => $this->driverRepository->getAllDrivers(),
            'deliveries' => $this->deliveryRepository->getFilteredDelivers($request),
        ]);
    }

    public function updateDeliverOrder(Request $request)
    {
        if ($request->deliveries) {
            $this->deliveryRepository->saveDeliveryOrder($request->deliveries);
        }
    }

    public function releaseDeliverOrder(ReleaseDeliveryRequest $request)
    {
        return $this->deliveryRepository->releaseDeliverOrder($request->all());
    }
}
