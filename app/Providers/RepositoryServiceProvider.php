<?php

namespace App\Providers;

use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\CashSettlementInterface;
use App\Interfaces\ContainerRepositoryInterface;
use App\Interfaces\DriverAreasRepositoryInterface;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\HBLRepositoryInterface;
use App\Interfaces\LoadedContainerRepositoryInterface;
use App\Interfaces\PickupExceptionRepositoryInterface;
use App\Interfaces\PickupRepositoryInterface;
use App\Interfaces\PriceRepositoryInterface;
use App\Interfaces\RoleRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\WarehouseRepositoryInterface;
use App\Interfaces\WarehousezoneRepositoryInterface;
use App\Interfaces\ZoneRepositoryInterface;
use App\Repositories\BranchRepository;
use App\Repositories\CashSettlementRepository;
use App\Repositories\ContainerRepositories;
use App\Repositories\DriverAreasRepository;
use App\Repositories\DriverRepository;
use App\Repositories\HBLRepository;
use App\Repositories\LoadedContainerRepository;
use App\Repositories\PickupExceptionRepository;
use App\Repositories\PickupRepository;
use App\Repositories\PriceRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Repositories\WareahouseZoneRepository;
use App\Repositories\WarehouseRepository;
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
        $this->app->bind(CashSettlementInterface::class, CashSettlementRepository::class);
        $this->app->bind(PickupExceptionRepositoryInterface::class, PickupExceptionRepository::class);
        $this->app->bind(ContainerRepositoryInterface::class, ContainerRepositories::class);
        $this->app->bind(PriceRepositoryInterface::class, PriceRepository::class);
        $this->app->bind(WarehouseRepositoryInterface::class, WarehouseRepository::class);
        $this->app->bind(LoadedContainerRepositoryInterface::class, LoadedContainerRepository::class);
        $this->app->bind(WarehousezoneRepositoryInterface::class, WareahouseZoneRepository::class);
        $this->app->bind(DriverAreasRepositoryInterface::class, DriverAreasRepository::class);
    }

    public function boot(): void
    {
    }
}
