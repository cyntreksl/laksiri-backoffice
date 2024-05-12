<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Interfaces\Api\Auth\AuthRepositoryInterface;
use App\Traits\ResponseAPI;

class LoginController extends Controller
{
    use ResponseAPI;

    public function __construct(
        private readonly AuthRepositoryInterface $authRepository,
    ) {
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('username', 'password');

        $ip = $request->ip();

        return $this->authRepository->login($credentials, $ip);
    }
}
