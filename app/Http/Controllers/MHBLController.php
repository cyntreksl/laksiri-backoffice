<?php

namespace App\Http\Controllers;

use App\Actions\Branch\GetDestinationBranches;
use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Interfaces\CountryRepositoryInterface;
use App\Interfaces\MHBLRepositoryInterface;
use App\Interfaces\OfficerRepositoryInterface;
use App\Models\HBL;
use App\Models\HBLPackage;
use App\Models\Mhbl;
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

    public function index()
    {
        return Inertia::render('MHBL/MHBLList');
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['userData', 'fromDate', 'toDate', 'cargoMode', 'createdBy', 'hblType', 'warehouse', 'isHold', 'paymentStatus']);

        return $this->mhblRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function create(Request $request)
    {
        $this->authorize('hbls.create');
        $data = $request->all();
        $hblIds = array_column($data['hbls'], 'id');

        $grand_total = HBL::whereIn('id', $hblIds)->get()->sum('grand_total');

        $hblPackages = HblPackage::whereIn('hbl_id', $hblIds)->get();
        $grand_volume = $hblPackages->sum('volume');
        $grand_weight = $hblPackages->sum('weight');
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
            'grandVolume' => $grand_volume,
            'grandWeight' => $grand_weight,
            'grandTotal' => $grand_total,
        ]);
    }

    public function store(Request $request)
    {
        $this->mhblRepository->storeHBL($request->all());
    }

    public function destroy(MHBL $mhbl)
    {
        $this->authorize('hbls.delete');

        $this->mhblRepository->deleteMHBL($mhbl);
    }
}
