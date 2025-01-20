<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignDriverRequest;
use Inertia\Inertia;

class DeliverController extends Controller
{
    public function assignDriver(AssignDriverRequest $request)
    {
        dd($request->all());

//        return $this->pickupRepository->assignDriverToPickups($request->all());
    }
}
