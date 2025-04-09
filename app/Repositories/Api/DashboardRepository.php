<?php

namespace App\Repositories\Api;

use App\Interfaces\Api\DashboardRepositoryInterface;
use App\Models\PickUp;
use App\Models\PickupException;
use App\Models\StatusLog;
use App\Traits\ResponseAPI;
use Illuminate\Support\Carbon;

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

        $recentActivities = StatusLog::where('user_id', $driverId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->join('pick_ups', 'status_logs.statusable_id', '=', 'pick_ups.id')
            ->select(
                'status_logs.id',
                'status_logs.statusable_type',
                'status_logs.status',
                'status_logs.description',
                'status_logs.created_at',
                'pick_ups.reference',
            )
            ->get()
            ->map(function ($item) {
                $modelName = str_replace('App\\Models\\', '', $item->statusable_type);

                return [
                    'id' => $item->id,
                    'type' => $modelName.' created',
                    'reference' => $item->reference,
                    'details' => $item->status,
                    'timestamp' => Carbon::parse($item->created_at)->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();

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
