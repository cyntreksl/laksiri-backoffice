<?php

namespace App\Repositories\Api\Auth;

use App\Interfaces\Api\Auth\AuthRepositoryInterface;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stevebauman\Location\Facades\Location;

class AuthRepository implements AuthRepositoryInterface
{
    use ResponseAPI;
    public function login(array $credentials, string $ip): JsonResponse
    {
        $position = Location::get($ip);

        if (Auth::attempt($credentials)) {
            if (Auth::user()->hasRole('driver')) {
                $token = Auth::user()->createToken('DriverAuthToken')->accessToken;

                activity()
                    ->causedBy(Auth::user())
                    ->withProperties(['ip' => $ip])
                    ->performedOn(Auth::user())
                    ->log('Driver Login');

                $this->logLoginAttempt(Auth::id(), now(), $position->longitude, $position->latitude, 'success');

                return $this->success('Login successful', ['token' => $token]);
            } else {
                activity()
                    ->causedBy(Auth::user())
                    ->withProperties(['ip' => $ip])
                    ->log('Failed login attempt');

                $this->logLoginAttempt(Auth::id(), now(), $position->longitude, $position->latitude, 'failed');

                return $this->error('Unauthorized', 401);
            }
        } else {
            activity()
                ->withProperties(['ip' => $ip])
                ->log("Failed login attempt from {{$credentials['username']}}");

            $this->logLoginAttempt(null, now(), $position->longitude, $position->latitude, 'failed');

            return $this->error('Invalid credentials', 401);
        }
    }

    private function logLoginAttempt($driverId, $time, $longitude, $latitude, $status): void
    {
        DB::table('driver_login_attempts')->insert([
            'driver_id' => $driverId,
            'time' => $time,
            'longitude' => $longitude,
            'latitude' => $latitude,
            'status' => $status,
        ]);
    }
}
