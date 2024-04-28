<?php

namespace App\Providers;

use App\Interfaces\PickupRepositoryInterface;
use App\Repositories\PickupRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PickupRepositoryInterface::class, PickupRepository::class);
    }

    public function boot(): void
    {
    }
}
