<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    public function countAssignedJobs(): int;

    public function countPickedJobs(): int;

    public function countPendingJobs(): int;

    public function countTotalHBLs(): int;

    public function countLoadedShipments(): int;

    public function countTotalContainers(): int;

    public function countTotalWarehouses(): int;

    public function countCashSettlements(): int;

    public function countTotalDrivers(): int;

    public function countDriverAssignedJobs(): int;

    public function getTotalDriverAssignedJobsByMonth(): Collection;

    public function countTotalPickups(): int;

    public function getTotalJobsByMonth(): Collection;

    public function getExceptionJobsByMonth(): Collection;

    public function getCollectedJobsByMonth(): Collection;

    public function getTotalHBLCountByMonth(): Collection;
}
