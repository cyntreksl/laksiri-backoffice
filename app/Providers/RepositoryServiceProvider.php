<?php

namespace App\Providers;

use App\Interfaces\HBLRepositoryInterface;
use App\Interfaces\PickupRepositoryInterface;
use App\Repositories\HBLRepository;
use App\Repositories\PickupRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PickupRepositoryInterface::class, PickupRepository::class);
        $this->app->bind(HBLRepositoryInterface::class, HBLRepository::class);
    }

    public function boot(): void
    {
    }
}
