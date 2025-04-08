<?php

namespace App\Repositories\Api;

use App\Interfaces\Api\DashboardRepositoryInterface;
use App\Models\PickUp;
use App\Models\PickupException;
use App\Traits\ResponseAPI;

class DashboardRepository implements DashboardRepositoryInterface
{
    use ResponseAPI;
    public function getDashboardStats(array $data)
    {
        $driverId = auth()->user()->id;

        $totalPickups = PickUp::where('driver_id', $driverId)->count();

        $pendingPickups = PickUp::where('driver_id', $driverId)
            ->where('system_status', PickUp::SYSTEM_STATUS_PICKUP_CREATED)
            ->count();

        $completedPickups = PickUp::where('driver_id', $driverId)
            ->where('system_status', PickUp::SYSTEM_STATUS_CARGO_COLLECTED)
            ->count();

        $pickupExceptions = PickupException::where('driver_id', $driverId)->count();

        $recentActivities = [
            PickUp::where('driver_id', $driverId)
                ->orderBy('pickup_date', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($pickup) {
                    return [
                        'id' => $pickup->id,
                        'type' => 'pickup created',
                        'reference' => $pickup->reference,
                        'details' => 'Pickup created successfully',
                        'timestamp' => now()->toDateTimeString(),
                    ];
                }),
        ];
        $recentActivities = collect($recentActivities)->flatten(1)->toArray();

        $data = [
            'total_pickups' => $totalPickups,
            'pending_pickups' => $pendingPickups,
            'completed_pickups' => $completedPickups,
            'pickup_exceptions' => $pickupExceptions,
            'recent_activities' => $recentActivities,
        ];
        return $this->success('Success', $data);

    }

}
