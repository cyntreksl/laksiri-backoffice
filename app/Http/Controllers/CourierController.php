<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Http\Requests\StoreCourierRequest;
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
        return Inertia::render('Courier/CourierList',

        );
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'hblType', 'status']);

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
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
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
