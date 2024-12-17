<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipperRequest;
use App\Interfaces\OfficerRepositoryInterface;
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

    public function storeshipper( Request $request)
    {

        $this->officerRepository->storeshipperOfficers($request->all());
    }
}
