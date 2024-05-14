<?php

namespace App\Providers;

use App\Interfaces\Api\Auth\AuthRepositoryInterface;
use App\Interfaces\Api\PickupRepositoryInterface;
use App\Repositories\Api\Auth\AuthRepository;
use App\Repositories\Api\PickupRepository;
use Illuminate\Support\ServiceProvider;

class ApiRepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PickupRepositoryInterface::class, PickupRepository::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
    }

    public function boot(): void
    {
    }
}
