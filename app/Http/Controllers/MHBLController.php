<?php

namespace App\Http\Controllers;

use App\Actions\Branch\GetDestinationBranches;
use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Enum\WarehouseType;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class MHBLController extends Controller
{
    use AuthorizesRequests;
    public function create(Request $request)
    {
        $this->authorize('hbls.create');
        $data = $request->all();
//        dd($data);

        return Inertia::render('MHBL/CreateMHBL', [
            'cargoTypes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
            'warehouses' => GetDestinationBranches::run(),
            'selectedCargoType' => $data['cargo_type'],
            'selectedHblType' => $data['hbl_type'],
            'selectedWarehouse' => ucfirst(strtolower($data['warehouse'])),
        ]);
    }
}
