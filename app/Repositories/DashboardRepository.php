<?php

namespace App\Repositories;

use App\Enum\ContainerStatus;
use App\Interfaces\DashboardRepositoryInterface;
use App\Models\Container;
use App\Models\HBL;
use App\Models\PickUp;
use App\Models\PickupException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function countAssignedJobs(): int
    {
        return PickUp::where('system_status', 2)->count();
    }

    public function countPickedJobs(): int
    {
        return PickUp::where('system_status', 3)->count();
    }

    public function countPendingJobs(): int
    {
        return PickUp::where('system_status', 1)->count();
    }

    public function countTotalHBLs(): int
    {
        return HBL::count();
    }

    public function countLoadedShipments(): int
    {
        return Container::whereIn('status', [
            ContainerStatus::LOADED->value,
            ContainerStatus::IN_TRANSIT->value
        ])->count();
    }

    public function countTotalContainers(): int
    {
        return Container::count();
    }

    public function countTotalWarehouses(): int
    {
        return HBL::whereIn('system_status', [4, 4.1])->count();
    }

    public function countCashSettlements(): int
    {
        return HBL::whereIn('system_status', [3, 3.1])->count();
    }

    public function countTotalDrivers(): int
    {
        return User::role('driver')
            ->currentBranch()
            ->count();
    }

    public function countDriverAssignedJobs(): int
    {
        return PickUp::whereIn('system_status', [PickUp::SYSTEM_STATUS_PICKUP_CREATED, PickUp::SYSTEM_STATUS_DRIVER_ASSIGNED])
            ->where('driver_id', '<>', null)
            ->count();
    }

    public function getTotalDriverAssignedJobsByMonth(): Collection
    {
        $allMonths = collect([
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
        ]);

        $data = PickUp::whereIn('system_status', [PickUp::SYSTEM_STATUS_PICKUP_CREATED, PickUp::SYSTEM_STATUS_DRIVER_ASSIGNED])
            ->where('driver_id', '<>', null)
            ->where('driver_assigned_at', '>=', Carbon::now()->subMonths(12))
            ->selectRaw('DATE_FORMAT(driver_assigned_at, "%b") as month_name, COUNT(driver_id) as count')
            ->groupBy('month_name')
            ->orderByRaw('FIELD(month_name, "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec")')
            ->pluck('count', 'month_name');

        return $allMonths->map(function ($month) use ($data) {
            return [$month => $data->get($month, 0)];
        })->collapse();
    }

    public function countTotalPickups(): int
    {
        return PickUp::count();
    }

    public function getTotalJobsByMonth(): Collection
    {
        $allMonths = collect([
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
        ]);

        $data = PickUp::where('driver_id', '<>', null)
            ->where('driver_assigned_at', '>=', Carbon::now()->subMonths(12))
            ->selectRaw('DATE_FORMAT(driver_assigned_at, "%b") as month_name, COUNT(driver_id) as count')
            ->groupBy('month_name')
            ->orderByRaw('FIELD(month_name, "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec")')
            ->pluck('count', 'month_name');

        return $allMonths->map(function ($month) use ($data) {
            return [$month => $data->get($month, 0)];
        })->collapse();
    }

    public function getExceptionJobsByMonth(): Collection
    {
        $allMonths = collect([
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
        ]);

        $data = PickupException::where('driver_assigned_at', '>=', Carbon::now()->subMonths(12))
            ->selectRaw('DATE_FORMAT(driver_assigned_at, "%b") as month_name, COUNT(driver_id) as count')
            ->groupBy('month_name')
            ->orderByRaw('FIELD(month_name, "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec")')
            ->pluck('count', 'month_name');

        return $allMonths->map(function ($month) use ($data) {
            return [$month => $data->get($month, 0)];
        })->collapse();
    }

    public function getCollectedJobsByMonth(): Collection
    {
        $allMonths = collect([
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
        ]);

        $data = PickUp::where('system_status', PickUp::SYSTEM_STATUS_CARGO_COLLECTED)
            ->where('driver_id', '<>', null)
            ->where('driver_assigned_at', '>=', Carbon::now()->subMonths(12))
            ->selectRaw('DATE_FORMAT(driver_assigned_at, "%b") as month_name, COUNT(driver_id) as count')
            ->groupBy('month_name')
            ->orderByRaw('FIELD(month_name, "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec")')
            ->pluck('count', 'month_name');

        return $allMonths->map(function ($month) use ($data) {
            return [$month => $data->get($month, 0)];
        })->collapse();
    }

    public function getTotalHBLCountByMonth(): Collection
    {
        $allMonths = collect([
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
        ]);
        $data = HBL::where('created_at', '>=', Carbon::now()->subMonths(12))
            ->selectRaw('DATE_FORMAT(created_at, "%b") as month_name, COUNT(*) as count')
            ->groupBy('month_name')
            ->orderByRaw('FIELD(month_name, "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec")')
            ->pluck('count', 'month_name');

        return $allMonths->map(function ($month) use ($data) {
            return [$month => $data->get($month, 0)];
        })->collapse();

    }
}
