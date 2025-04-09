<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Interfaces\Api\DashboardRepositoryInterface;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardRepositoryInterface $dashboardRepository,
    ) {}

    public function stats(Request $request)
    {
        return $this->dashboardRepository->getDashboardStats($request->all());
    }
}
