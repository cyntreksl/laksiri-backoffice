<?php

namespace App\Providers;

use App\Interfaces\Api\PickupRepositoryInterface;
use App\Repositories\Api\PickupRepository;
use Illuminate\Support\ServiceProvider;

class ApiRepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PickupRepositoryInterface::class, PickupRepository::class);
    }

    public function boot(): void
    {
    }
}
