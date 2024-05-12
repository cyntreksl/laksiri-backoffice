<?php

namespace App\Interfaces\Api\Auth;

use Illuminate\Http\JsonResponse;

interface AuthRepositoryInterface
{
    /**
     * Attempt to log in with provided credentials.
     *
     * @param  array  $credentials  The user credentials
     * @param  string  $ip  The IP address of the request
     * @return JsonResponse Result of the login attempt
     *
     * @method  POST api/login
     */
    public function login(array $credentials, string $ip): JsonResponse;
}
