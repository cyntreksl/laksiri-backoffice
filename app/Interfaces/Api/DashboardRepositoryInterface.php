<?php

namespace App\Interfaces\Api;

interface DashboardRepositoryInterface
{
    public function getDashboardStats(array $data);
}
