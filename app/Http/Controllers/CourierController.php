<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Http\Requests\StoreCourierRequest;
use App\Http\Requests\UpdateCourierRequest;
use App\Http\Requests\UpdateCourierStatusRequest;
use App\Interfaces\CountryRepositoryInterface;
use App\Interfaces\CourierAgentRepositoryInterface;
use App\Interfaces\CourierRepositoryInterface;
use App\Interfaces\PackageTypeRepositoryInterface;
use App\Models\Courier;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourierController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly PackageTypeRepositoryInterface $packageTypeRepository,
        private readonly CountryRepositoryInterface $countryRepository,
        private readonly CourierRepositoryInterface $CourierRepository,
        private readonly CourierAgentRepositoryInterface $CourierAgentRepository,

    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Courier/CourierList');
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'deliveryType', 'status']);

        return $this->CourierRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Courier/CreateCourier', [
            'cargoTypes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
            'packageTypes' => $this->packageTypeRepository->getPackageTypes(),
            'countryCodes' => $this->countryRepository->getAllPhoneCodes(),
            'courierAgents' => $this->CourierAgentRepository->getAllCourierAgents(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourierRequest $request)
    {
        $this->CourierRepository->storeCourier($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function changeCourierStatus(UpdateCourierStatusRequest $request)
    {
        $this->CourierRepository->changeStatus($request['couriers'], $request['status']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Courier $courier)
    {
        $this->authorize('courier.edit');

        return Inertia::render('Courier/EditCourier', [
            'courier' => $courier->load('packages'),
            'cargoTypes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
            'packageTypes' => $this->packageTypeRepository->getPackageTypes(),
            'countryCodes' => $this->countryRepository->getAllPhoneCodes(),
            'courierAgents' => $this->CourierAgentRepository->getAllCourierAgents(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Courier $courier, UpdateCourierRequest $request)
    {
        $this->CourierRepository->updateCourier($courier, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Courier $courier)
    {
        $this->authorize('courier.delete');

        $this->CourierRepository->deleteCourier($courier);
    }
}
