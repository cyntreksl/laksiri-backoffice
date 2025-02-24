<?php

namespace App\Providers;

use App\Interfaces\AnyFileUploadRepositoryInterface;
use App\Interfaces\BondedWarehouseRepositoryInterface;
use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\CallCenter\BonedAreaRepositoryInterface;
use App\Interfaces\CallCenter\CashierRepositoryInterface;
use App\Interfaces\CallCenter\DeliveryRepositoryInterface;
use App\Interfaces\CallCenter\ExaminationRepositoryInterface;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use App\Interfaces\CallCenter\ReceptionRepositoryInterface;
use App\Interfaces\CallCenter\UserFeedbackRepositoryInterface;
use App\Interfaces\CallCenter\VerificationRepositoryInterface;
use App\Interfaces\CashSettlementInterface;
use App\Interfaces\ContainerRepositoryInterface;
use App\Interfaces\CountryRepositoryInterface;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\DashboardRepositoryInterface;
use App\Interfaces\DriverAreasRepositoryInterface;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\ExceptionNameRepositoryInterface;
use App\Interfaces\FileManagerRepositoryInterface;
use App\Interfaces\HBLRepositoryInterface;
use App\Interfaces\LoadedContainerRepositoryInterface;
use App\Interfaces\MHBLRepositoryInterface;
use App\Interfaces\NotificationMailRepositoryInterface;
use App\Interfaces\OfficerRepositoryInterface;
use App\Interfaces\PackagePriceRepositoryInterface;
use App\Interfaces\PackageTypeRepositoryInterface;
use App\Interfaces\PickupExceptionRepositoryInterface;
use App\Interfaces\PickupRepositoryInterface;
use App\Interfaces\PriceRepositoryInterface;
use App\Interfaces\RoleRepositoryInterface;
use App\Interfaces\SettingRepositoryInterface;
use App\Interfaces\ShipperConsigneeRepositoryInterface;
use App\Interfaces\UnloadingIssuesRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\WarehouseRepositoryInterface;
use App\Interfaces\WarehousezoneRepositoryInterface;
use App\Interfaces\CourierAgentRepositoryInterface;
use App\Interfaces\ZoneRepositoryInterface;
use App\Repositories\AnyFileUploadRepository;
use App\Repositories\BondedWarehouseRepository;
use App\Repositories\BranchRepository;
use App\Repositories\CallCenter\BonedAreaRepository;
use App\Repositories\CallCenter\CashierRepository;
use App\Repositories\CallCenter\DeliveryRepository;
use App\Repositories\CallCenter\ExaminationRepository;
use App\Repositories\CallCenter\QueueRepository;
use App\Repositories\CallCenter\ReceptionRepository;
use App\Repositories\CallCenter\UserFeedbackRepository;
use App\Repositories\CallCenter\VerificationRepository;
use App\Repositories\CashSettlementRepository;
use App\Repositories\ContainerRepositories;
use App\Repositories\CountryRepository;
use App\Repositories\CourierAgentRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\DashboardRepository;
use App\Repositories\DriverAreasRepository;
use App\Repositories\DriverRepository;
use App\Repositories\ExceptionNameRepository;
use App\Repositories\FileManagerRepository;
use App\Repositories\HBLRepository;
use App\Repositories\LoadedContainerRepository;
use App\Repositories\MHBLRepository;
use App\Repositories\NotificationMailRepository;
use App\Repositories\OfficerRepository;
use App\Repositories\PackagePriceRepository;
use App\Repositories\PackageTypeRepository;
use App\Repositories\PickupExceptionRepository;
use App\Repositories\PickupRepository;
use App\Repositories\PriceRepository;
use App\Repositories\RoleRepository;
use App\Repositories\SettingRepository;
use App\Repositories\ShipperConsigneeRepository;
use App\Repositories\UnloadingIssuesRepository;
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
        $this->app->bind(BondedWarehouseRepositoryInterface::class, BondedWarehouseRepository::class);
        $this->app->bind(UnloadingIssuesRepositoryInterface::class, UnloadingIssuesRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->bind(ExceptionNameRepositoryInterface::class, ExceptionNameRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(FileManagerRepositoryInterface::class, FileManagerRepository::class);
        $this->app->bind(PackagePriceRepositoryInterface::class, PackagePriceRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(PackageTypeRepositoryInterface::class, PackageTypeRepository::class);
        $this->app->bind(ShipperConsigneeRepositoryInterface::class, ShipperConsigneeRepository::class);
        $this->app->bind(NotificationMailRepositoryInterface::class, NotificationMailRepository::class);
        $this->app->bind(CourierAgentRepositoryInterface::class ,CourierAgentRepository::class);

        // call center repositories
        $this->app->bind(\App\Interfaces\CallCenter\HBLRepositoryInterface::class, \App\Repositories\CallCenter\HBLRepository::class);
        $this->app->bind(QueueRepositoryInterface::class, QueueRepository::class);
        $this->app->bind(VerificationRepositoryInterface::class, VerificationRepository::class);
        $this->app->bind(CashierRepositoryInterface::class, CashierRepository::class);
        $this->app->bind(BonedAreaRepositoryInterface::class, BonedAreaRepository::class);
        $this->app->bind(ExaminationRepositoryInterface::class, ExaminationRepository::class);
        $this->app->bind(UserFeedbackRepositoryInterface::class, UserFeedbackRepository::class);
        $this->app->bind(AnyFileUploadRepositoryInterface::class, AnyFileUploadRepository::class);
        $this->app->bind(CountryRepositoryInterface::class, CountryRepository::class);
        $this->app->bind(OfficerRepositoryInterface::class, OfficerRepository::class);
        $this->app->bind(MHBLRepositoryInterface::class, MHBLRepository::class);
        $this->app->bind(DeliveryRepositoryInterface::class, DeliveryRepository::class);
        $this->app->bind(ReceptionRepositoryInterface::class, ReceptionRepository::class);
    }

    public function boot(): void {}
}
