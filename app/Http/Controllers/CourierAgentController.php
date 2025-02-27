<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourierAgentRequest;
use App\Interfaces\CountryRepositoryInterface;
use App\Interfaces\CourierAgentRepositoryInterface;
use App\Models\CourierAgent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourierAgentController extends Controller
{
    public function __construct(
        private readonly CourierAgentRepositoryInterface $courierAgentRepository,
        private readonly CountryRepositoryInterface $countryRepository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('CourierAgent/CourierAgentList', [
            'courierAgents' => $this->courierAgentRepository->getAllCourierAgents(),
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate']);

        $dataset = $this->courierAgentRepository->dataset($limit, $page, $order, $dir, $search, $filters);

        return $dataset;
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('CourierAgent/CreateCourierAgent',
            [
                'countryCodes' => $this->countryRepository->getAllPhoneCodes(),
            ]

        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourierAgentRequest $request)
    {
        $this->courierAgentRepository->storeCourierAgent($request->all());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $courierAgent = CourierAgent::findOrFail($id);

        return Inertia::render('CourierAgent/EditCourierAgent',
            [
                'courierAgent' => $courierAgent,
                'countryCodes' => $this->countryRepository->getAllPhoneCodes(),
            ]

        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->courierAgentRepository->updateCourierAgent($request->all(), $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->courierAgentRepository->destroyCourierAgent($id);
    }
}
