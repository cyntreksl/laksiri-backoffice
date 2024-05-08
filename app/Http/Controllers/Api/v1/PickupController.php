<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\PickUp;

class PickupController extends Controller
{
    public function index()
    {
        $pickups = PickUp::where('driver_id', auth()->id())
            ->orderBy('pickup_order')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Pending pickup list received successfully!',
            'data' => $pickups,
        ], 200);
    }
}
