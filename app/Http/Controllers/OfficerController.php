<?php

namespace App\Http\Controllers;

use App\Interfaces\OfficerRepositoryInterface;
use App\Models\Officer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OfficerController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly OfficerRepositoryInterface $officerRepository
    ) {

    }

    public function index()
    {
        return Inertia::render('Setting/ShippersConsignees/OfficerList', [
            'allOfficers' => $this->officerRepository->getAllofficers(),
        ]);
    }

    public function store(Request $request)
    {

        $this->officerRepository->storeshipperOfficers($request->all());
    }

    public function edit($id)
    {
        $officer = Officer::withoutGlobalScopes()->findOrFail($id);

        return Inertia::render('Setting/ShippersConsignees/UpdateOfficer', [
            'officer' => $officer,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->officerRepository->updateShipper($request->all(), $id);
    }
    public function destroy($id)
    {
        $this->officerRepository->destroyShippers($id);

    }
}
