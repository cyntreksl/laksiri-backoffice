<?php

namespace App\Http\Controllers;

use App\Interfaces\DriverRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashSettlementController extends Controller
{

    public function __construct(
        private readonly DriverRepositoryInterface $driverRepository)
    {
    }

    public function index()
    {
        $drivers = $this->driverRepository->getAllDrivers();
        $officers = [];
        return Inertia::render('CashSettlement/CashSettlementList', ['drivers' => $drivers, 'officers' => $officers]);
    }
}
