<?php

namespace App\Providers;

use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\HBLRepositoryInterface;
use App\Interfaces\PickupRepositoryInterface;
use App\Interfaces\RoleRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\ZoneRepositoryInterface;
use App\Repositories\BranchRepository;
use App\Repositories\DriverRepository;
use App\Repositories\HBLRepository;
use App\Repositories\PickupRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Repositories\ZoneRepository;
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
        $this->app->bind(DriverRepositoryInterface::class, DriverRepository::class);
        $this->app->bind(ZoneRepositoryInterface::class, ZoneRepository::class);
    }

    public function boot(): void
    {
    }
}
