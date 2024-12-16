<?php

namespace App\Http\Controllers;

use App\Interfaces\OfficerRepositoryInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class OfficerController extends  Controller
{
    use AuthorizesRequests;

    public function __construct (
        private readonly OfficerRepositoryInterface $officerRepository
    )
    {

    }
    public function index()
    {
        return Inertia::render('Setting/ShippersConsignees/OfficerList');
    }
    public function create()
    {
        return Inertia::render('Setting/ShippersConsignees/OfficerCreate');

    }

}
