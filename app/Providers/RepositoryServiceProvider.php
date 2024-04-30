<?php

namespace App\Providers;

use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\HBLRepositoryInterface;
use App\Interfaces\PickupRepositoryInterface;
use App\Interfaces\RoleRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Repositories\BranchRepository;
use App\Repositories\HBLRepository;
use App\Repositories\PickupRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PickupRepositoryInterface::class, PickupRepository::class);
        $this->app->bind(HBLRepositoryInterface::class, HBLRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(BranchRepositoryInterface::class, BranchRepository::class);
    }

    public function boot(): void
    {
    }
}
