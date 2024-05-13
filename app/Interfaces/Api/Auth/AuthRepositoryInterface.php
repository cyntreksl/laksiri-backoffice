<?php

namespace App\Interfaces\Api\Auth;

use Illuminate\Http\JsonResponse;

interface AuthRepositoryInterface
{
    /**
     * Attempt to log in with provided credentials.
     *
     * @return JsonResponse Result of the login attempt
     *
     * @method  POST api/login
     */
    public function login(array $data): JsonResponse;
}
