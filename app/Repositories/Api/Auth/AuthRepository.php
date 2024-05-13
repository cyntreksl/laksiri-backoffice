<?php

namespace App\Repositories\Api\Auth;

use App\Interfaces\Api\Auth\AuthRepositoryInterface;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthRepository implements AuthRepositoryInterface
{
    use ResponseAPI;

    public function login(array $data): JsonResponse
    {
        $credentials = [
            'username' => $data['username'],
            'password' => $data['password'],
        ];

        $longitude = null;
        $latitude = null;

        if (isset($data['location'])) {
            $longitude = $data['location']['longitude'];
            $latitude = $data['location']['latitude'];
        }

        $meta_data = null;

        if (isset($data['meta_data'])) {
            $meta_data = $data['meta_data'];
        }

        if (Auth::attempt($credentials)) {
            if (Auth::user()->hasRole('driver')) {
                $token = Auth::user()->createToken('DriverAuthToken')->accessToken;

                activity()
                    ->causedBy(Auth::user())
                    ->withProperties(['ip' => request()->ip()])
                    ->performedOn(Auth::user())
                    ->log('Driver Login');

                $this->logLoginAttempt(Auth::id(), now(), $longitude, $latitude, $meta_data, 'success');

                return $this->success('Login successful', [
                    'token' => $token,
                    'user' => [
                        'username' => Auth::user()->username,
                        'name' => Auth::user()->name,
                        'profile_photo_url' => Auth::user()->profile_photo_url,
                        'role' => Auth::user()->roles[0]->name,
                    ],
                ]);
            } else {
                activity()
                    ->causedBy(Auth::user())
                    ->withProperties(['ip' => request()->ip()])
                    ->log('Failed login attempt');

                $this->logLoginAttempt(Auth::id(), now(), $longitude, $latitude, $meta_data, 'failed');

                return $this->error('Unauthorized', 401);
            }
        } else {
            activity()
                ->withProperties(['ip' => request()->ip()])
                ->log("Failed login attempt from {{$credentials['username']}}");

            $this->logLoginAttempt(null, now(), $longitude, $latitude, $meta_data, 'failed');

            return $this->error('Invalid credentials', 401);
        }
    }

    private function logLoginAttempt($driverId, $time, $longitude, $latitude, $meta_data, $status): void
    {
        DB::table('driver_login_attempts')->insert([
            'driver_id' => $driverId,
            'time' => $time,
            'longitude' => $longitude,
            'latitude' => $latitude,
            'meta_data' => json_encode($meta_data),
            'status' => $status,
        ]);
    }
}
