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

    /**
     * Login
     *
     * Authenticate user and return token
     *
     * @unauthenticated
     * @group Authentication
     * @response 200 {
     * "message": "Login successful",
     * "error": false,
     * "code": 200,
     * "results": {
     * "token": "{YOUR_AUTH_KEY}",
     * "user": {
     * "username": "Driver",
     * "name": "Driver 1",
     * "profile_photo_url": "https://ui-avatars.com/api/?name=D+1&color=7F9CF5&background=EBF4FF",
     * "role": "driver"
     * }
     * }
     * }
     */
    public function login(LoginRequest $request)
    {
        return $this->authRepository->login($request->all());
    }
}
