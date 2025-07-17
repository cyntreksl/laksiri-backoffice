<?php

namespace App\Http\Controllers;

use App\Actions\Branch\GetDestinationBranches;
use App\Actions\HBL\GetHBLByIdWithPackages;
use App\Actions\MHBL\GetMHBLById;
use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Http\Requests\StoreMHBLRequest;
use App\Http\Requests\UpdateMHBLRequest;
use App\Interfaces\CountryRepositoryInterface;
use App\Interfaces\HBLRepositoryInterface;
use App\Interfaces\MHBLRepositoryInterface;
use App\Interfaces\OfficerRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\HBL;
use App\Models\HBLPackage;
use App\Models\MHBL;
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
        private readonly UserRepositoryInterface $userRepository,
        private readonly HBLRepositoryInterface $HBLRepository,
    ) {}

    public function index()
    {
        $this->authorize('mhbls.index');

        return Inertia::render('MHBL/MHBLList', [
            'warehouses' => GetDestinationBranches::run(),
            'users' => $this->userRepository->getUsers(['customer']),
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['userData', 'fromDate', 'toDate', 'cargoMode', 'createdBy', 'hblType', 'warehouse', 'isHold', 'paymentStatus']);

        return $this->mhblRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function create(Request $request)
    {
        $this->authorize('hbls.create');

        $data = $request->all();

        $grand_total = HBL::whereIn('id', $data['hbls'])->get()->sum('grand_total');

        $hblPackages = HblPackage::whereIn('hbl_id', $data['hbls'])->get();

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
            'selectedHblType' => HBLType::GIFT->value,
            'selectedWarehouse' => ucfirst(strtolower($data['warehouse'])),
            'shippers' => $this->officerRepository->getShippers(),
            'consignees' => $this->officerRepository->getConsignees(),
            'packages' => $packages,
            'hblIds' => $data['hbls'],
            'grandVolume' => $grand_volume,
            'grandWeight' => $grand_weight,
            'grandTotal' => $grand_total,
        ]);
    }

    public function store(StoreMHBLRequest $request)
    {
        $this->mhblRepository->storeHBL($request->all());
    }

    public function destroy(MHBL $mhbl)
    {
        $this->authorize('hbls.delete');

        $this->mhblRepository->deleteMHBL($mhbl);
    }

    public function edit(MHBL $mhbl)
    {
        $this->authorize('hbls.edit');

        $mhblData = MHBL::where('id', $mhbl->id)
            ->with([
                'hbls.packages',
                'warehouse',
                'shipper',
                'consignee',
            ])->first();

        if ($mhblData && $mhblData->hbls) {
            $hblPackages = $mhblData->hbls->flatMap(function ($hbl) {
                return $hbl->packages->map(function ($package) use ($hbl) {
                    return [
                        'hbl' => $hbl->hbl_number,
                        'hbl_id' => $hbl->id,
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
                        'weight' => $package->weight ?? 0,
                    ];
                });
            });
        }

        return Inertia::render('MHBL/EditMHBL', [
            'cargoTypes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
            'warehouses' => GetDestinationBranches::run(),
            'countryCodes' => $this->countryRepository->getAllPhoneCodes(),
            'shippers' => $this->officerRepository->getShippers(),
            'consignees' => $this->officerRepository->getConsignees(),
            'mhbl' => $mhblData,
            'hblIds' => $mhblData->hbls->pluck('id')->toArray(),
            'packages' => $hblPackages ?? [],
        ]);
    }

    public function addNewHBL(Request $request)
    {
        return $this->mhblRepository->addNewHBL($request->all());
    }

    public function update(MHBL $mhbl, UpdateMHBLRequest $request)
    {
        $this->mhblRepository->updateMHBL($mhbl, $request->all());
    }

    public function getUnloadedMHBLs(Request $request)
    {
        return $this->mhblRepository->getUnloadedMHBLs($request->all());
    }

    public function getLoadedMHBLsToContainer(Request $request)
    {
        return $this->mhblRepository->getContainerLoadedMHBLs($request->all());
    }

    public function show($mhbl_id)
    {
        return response()->json([
            'mhbl' => GetMHBLById::run($mhbl_id),
        ]);
    }

    public function hblListDownloadPDF(MHBL $mhbl)
    {
        $this->authorize('mhbls.download hbl list');

        return $this->mhblRepository->hblListDownloadPDF($mhbl);
    }

    public function downloadMHBLPDF(MHBL $mhbl)
    {
        $this->authorize('hbls.download pdf');

        // Get all HBLs for this MHBL
        $hbls = $mhbl->hbls;

        // Get all HBLs with their packages
        $hblsWithPackages = $hbls->map(function ($hbl) {
            return GetHBLByIdWithPackages::run($hbl->id);
        });

        return $this->mhblRepository->downloadMHBLPDF($mhbl, $hblsWithPackages);
    }
}
