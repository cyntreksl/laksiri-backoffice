<?php

namespace App\Http\Controllers;

use App\Enum\HBLType;
use Inertia\Inertia;

class BondedWarehouseController extends Controller
{
    public function index()
    {
        return Inertia::render('Arrival/BondedWarehouseList', [
            'hblTypes' => HBLType::cases(),
        ]);
    }
}
