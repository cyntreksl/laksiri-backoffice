<?php

namespace App\Providers;

use App\Interfaces\HBLRepositoryInterface;
use App\Interfaces\PickupRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Repositories\HBLRepository;
use App\Repositories\PickupRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PickupRepositoryInterface::class, PickupRepository::class);
        $this->app->bind(HBLRepositoryInterface::class, HBLRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    public function boot(): void
    {
    }
}
