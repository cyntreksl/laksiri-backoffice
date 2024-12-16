<?php

namespace App\Http\Controllers;

use App\Actions\Branch\GetDestinationBranches;
use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Interfaces\CountryRepositoryInterface;
use App\Interfaces\MHBLRepositoryInterface;
use App\Interfaces\OfficerRepositoryInterface;
use App\Models\HBLPackage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MHBLController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly OfficerRepositoryInterface $officerRepository,
        private readonly CountryRepositoryInterface $countryRepository,
        private readonly MHBLRepositoryInterface $mhblRepository,
    ) {
    }

    public function create(Request $request)
    {
        $this->authorize('hbls.create');
        $data = $request->all();
        $hblIds = array_column($data['hbls'], 'id');

        $hblPackages = HblPackage::whereIn('hbl_id', $hblIds)->get();
        $additional_charge = $hblPackages->sum('freight_charge');
        $additional_charge = $hblPackages->sum('bill_charge');
        $additional_charge = $hblPackages->sum('other_charge');
        $additional_charge = $hblPackages->sum('discount');
        $additional_charge = $hblPackages->sum('additional_charge');
        $packages = $hblPackages->map(function ($package) {
            return [
                'id' => $package->id,
                'type' => $package->package_type ?? '',
                'length' => $package->length ?? 0,
                'width' => $package->width ?? 0,
                'height' => $package->height ?? 0,
                'quantity' => $package->quantity ?? 1,
                'volume' => $package->volume ?? 0,
                'totalWeight' => $package->total_weight ?? 0,
                'remarks' => $package->remarks ?? '',
                'packageRule' => $package->package_rule ?? 0,
                'measure_type' => $package->measure_type ?? 'cm',
            ];
        });

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
            'packages' => $packages,
            'hblIds' => $hblIds,
        ]);
    }

    public function store(Request $request)
    {
        $this->mhblRepository->storeHBL($request->all());
    }
}
