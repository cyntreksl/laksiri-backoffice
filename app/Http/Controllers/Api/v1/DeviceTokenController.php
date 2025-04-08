<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceTokenController extends Controller
{
    use ResponseAPI;

    public function registerDevice(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'required|string|in:ios,android',
            'device_id' => 'nullable|string',
        ]);

        $userId = Auth::id();

        $deviceToken = DeviceToken::updateOrCreate(
            [
                'user_id' => $userId,
                'device_id' => $request->device_id,
            ],
            [
                'token' => $request->token,
                'platform' => $request->platform,
                'is_active' => true,
            ]
        );

        return $this->success('Device registered successfully', $deviceToken);

    }
}
