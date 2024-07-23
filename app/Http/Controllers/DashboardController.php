<?php

namespace App\Http\Controllers;

use App\Interfaces\DashboardRepositoryInterface;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardRepositoryInterface $dashboardRepository,
    ) {
    }

    public function index()
    {
        return Inertia::render('Dashboard', [
            'assignedJobs' => $this->dashboardRepository->countAssignedJobs(),
            'pickedJobs' => $this->dashboardRepository->countPickedJobs(),
            'pendingJobs' => $this->dashboardRepository->countPendingJobs(),
            'totalHBLs' => $this->dashboardRepository->countTotalHBLs(),
            'loadedShipments' => $this->dashboardRepository->countLoadedShipments(),
            'totalContainers' => $this->dashboardRepository->countTotalContainers(),
            'warehouses' => $this->dashboardRepository->countTotalWarehouses(),
            'cashSettlements' => $this->dashboardRepository->countCashSettlements(),
            'totalDrivers' => $this->dashboardRepository->countTotalDrivers(),
            'driverAssignedJobs' => $this->dashboardRepository->countDriverAssignedJobs(),
            'driverChartData' => $this->dashboardRepository->getTotalDriverAssignedJobsByMonth(),
        ]);
    }
}
