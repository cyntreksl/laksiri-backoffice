<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Models\AirLine;
use App\Models\Branch;
use App\Models\Container;
use Inertia\Inertia;

class ThirdPartyShipmentController extends Controller
{
    public function create()
    {
        $agents = Branch::thirdpartyAgents()->get();
        $cargoTypes = CargoType::cases();
        $hblTypes = HBLType::cases();
        $shipments = Container::get();
        $airLines = AirLine::pluck('name', 'id');

        return Inertia::render('ThirdPartyShipments/ThirdPartyShipmentCreate',
            compact('agents', 'cargoTypes', 'hblTypes', 'shipments', 'airLines'));
    }
}
