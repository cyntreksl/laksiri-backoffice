<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOfficerRequest;
use App\Http\Requests\UpdateOfficerRequest;
use App\Interfaces\CountryRepositoryInterface;
use App\Interfaces\OfficerRepositoryInterface;
use App\Models\Officer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class OfficerController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly OfficerRepositoryInterface $officerRepository,
        private readonly CountryRepositoryInterface $countryRepository,
    ) {

    }

    public function index()
    {
        return Inertia::render('Setting/ShippersConsignees/OfficerList', [
            'allOfficers' => $this->officerRepository->getAllofficers(),
            'countryCodes' => $this->countryRepository->getAllPhoneCodes(),
        ]);
    }

    public function store(StoreOfficerRequest $request)
    {
        $this->officerRepository->storeshipperOfficers($request->all());
    }

    public function edit($id)
    {
        $officer = Officer::findOrFail($id);

        return Inertia::render('Setting/ShippersConsignees/UpdateOfficer', [
            'officer' => $officer,
            'countryCodes' => $this->countryRepository->getAllPhoneCodes()
        ]);
    }

    public function update(UpdateOfficerRequest $request, $id)
    {
        $this->officerRepository->updateShipper($request->all(), $id);
    }

    public function destroy($id)
    {
        $this->officerRepository->destroyShippers($id);

    }
}
