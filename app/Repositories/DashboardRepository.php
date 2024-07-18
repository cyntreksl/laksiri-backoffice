<?php

namespace App\Repositories;

use App\Enum\ContainerStatus;
use App\Interfaces\DashboardRepositoryInterface;
use App\Models\Container;
use App\Models\HBL;
use App\Models\PickUp;

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
        return Container::where('status', ContainerStatus::LOADED->value)->count();
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
}
