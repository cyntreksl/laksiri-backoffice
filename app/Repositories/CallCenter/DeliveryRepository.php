<?php

namespace App\Repositories\CallCenter;

use App\Actions\Delivery\CreateHBLDelivery;
use App\Actions\Delivery\ReleaseHBLDelivery;
use App\Actions\Delivery\SaveDeliveryOrder;
use App\Actions\HBL\GetHBLsByIDs;
use App\Actions\HBL\MarkAsDriverAssigned;
use App\Actions\HBL\UnassignDriver;
use App\Factory\Delivery\FilterFactory;
use App\Http\Resources\HBLDeliverResource;
use App\Interfaces\CallCenter\DeliveryRepositoryInterface;
use App\Models\HBL;
use App\Models\HBLDeliver;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryRepository implements DeliveryRepositoryInterface
{
    use ResponseAPI;

    public function assignDriverToDeliver(array $data): void
    {
        $hblList = GetHBLsByIDs::run($data['job_ids']);

        foreach ($hblList as $hbl) {
            MarkAsDriverAssigned::run($hbl);

            CreateHBLDelivery::run($data['driver_id'], $hbl);
        }
    }

    public function getPendingDeliverForDriver(): JsonResponse
    {
        try {
            $query = HBLDeliver::query()->assignedToDriver()->with('hbl');

            $delivers = $query->orderBy('deliver_order')->get();

            $pendingDeliverResource = HBLDeliverResource::collection($delivers);

            return $this->success('Pending deliver list received successfully!', $pendingDeliverResource);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function showDeliver(HBLDeliver $hblDeliver): JsonResponse
    {
        try {
            $deliverResource = new HBLDeliverResource($hblDeliver->load('hbl'));

            return $this->success('Success', $deliverResource);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function getFilteredDelivers(Request $request)
    {
        $query = HBLDeliver::query();

        // Filter by related HBL where is_released = 0
        $query->whereHas('hbl', function ($q) {
            $q->where('is_released', 0);
        });

        if ($request->filled('driverId')) {
            FilterFactory::apply($query, ['driverBy' => $request->driverId]);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query
            ->orderBy('deliver_order')
            ->with('hbl')
            ->get();
    }

    public function saveDeliveryOrder(array $deliveries): void
    {
        foreach ($deliveries as $delivery) {
            SaveDeliveryOrder::run($delivery);
        }
    }

    public function releaseDeliverOrder(array $data): JsonResponse
    {
        $hbl = HBL::withoutGlobalScopes()->where('id', $data['hbl_id'])->with('packages')->first();

        if (! $hbl) {
            return $this->error('HBL not found.', [], 404);
        }

        $releasedPackageIds = collect($data['released_packages'])->pluck('id')->toArray();

        if (! empty(array_diff($releasedPackageIds, $hbl->packages->pluck('id')->toArray()))) {
            return $this->error('Invalid package(s) detected.', [], 422);
        }

        ReleaseHBLDelivery::run($hbl, $data);

        return $this->success('HBL Delivered successfully!', [], 200);
    }

    public function unassignDriverFromDeliver(HBL $hbl): void
    {
        UnassignDriver::run($hbl);
    }
}
