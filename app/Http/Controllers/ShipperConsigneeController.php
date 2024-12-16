<?php

namespace App\Http\Controllers;

use App\Interfaces\ShipperConsigneeRepositoryInterface;
use App\Models\ShippersConsignees;
use App\Repositories\ShipperConsigneeRepository;
use http\Env\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class ShipperConsigneeController extends Controller
{
    use AuthorizesRequests;
    public function __construct (
        private readonly ShipperConsigneeRepositoryInterface $shipperConsigneeRepository
    )
    {

    }
    public function index()
    {
        return Inertia::render('Setting/ShippersConsignees/OfficerList');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:shippers_and_consignees,email',
            'contact' => 'required|string|max:15',
            'type' => 'required|in:shipper,consignee',
        ]);

        ShippersConsignees::store($validated);


    }

}
