<?php

namespace App\Http\Controllers;

use App\Actions\Branch\GetDestinationBranches;
use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Enum\WarehouseType;
use App\Interfaces\CountryRepositoryInterface;
use App\Interfaces\OfficerRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class MHBLController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly OfficerRepositoryInterface $officerRepository,
        private readonly CountryRepositoryInterface $countryRepository,
    ) {
    }
    public function create(Request $request)
    {
        $this->authorize('hbls.create');
        $data = $request->all();

        return Inertia::render('MHBL/CreateMHBL', [
            'cargoTypes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
            'warehouses' => GetDestinationBranches::run(),
            'countryCodes' => $this->countryRepository->getAllPhoneCodes(),
            'selectedCargoType' => $data['cargo_type'],
            'selectedHblType' => $data['hbl_type'],
            'selectedWarehouse' => ucfirst(strtolower($data['warehouse'])),
            'shippers' => $this->officerRepository->getShippers(),
            'consignees' => $this->officerRepository->getConsignees(),
        ]);
    }
}
