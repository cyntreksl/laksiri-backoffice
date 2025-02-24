<?php

namespace App\Observers;

use App\Actions\BranchPrice\GetPriceRulesByCargoModeAndHBLType;
use App\Actions\HBLPackageRuleData\UpdateHBLPackageRuleData;
use App\Actions\User\CreateUser;
use App\Actions\User\GetUserCurrentBranchID;
use App\Interfaces\NotificationMailRepositoryInterface;
use App\Models\HBL;
use App\Models\User;
use Spatie\Permission\Models\Role;

class HBLObserver
{
    protected static array $cascade_relations = ['packages', 'status', 'hblPayment'];

    protected $notificationMailRepository;

    public function __construct(NotificationMailRepositoryInterface $notificationMailRepository)
    {
        $this->notificationMailRepository = $notificationMailRepository;
    }

    /**
     * Handle the PickUp "created" event.
     */
    public function created(HBL $hbl): void
    {
        Role::updateOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        $shipperData = [
            'role' => 'customer',
            'name' => $hbl->hbl_name,
            'email' => $hbl->email,
            'username' => $hbl->contact_number,
            'contact' => $hbl->contact_number,
            'password' => bcrypt('password'),
            'primary_branch_id' => GetUserCurrentBranchID::run(),
            'is_shipper' => true,
        ];

        $consigneeData = [
            'role' => 'customer',
            'name' => $hbl->consignee_name,
            'username' => $hbl->consignee_contact,
            'contact' => $hbl->consignee_contact,
            'password' => bcrypt('password'),
            'primary_branch_id' => GetUserCurrentBranchID::run(),
            'is_consignee' => true,
        ];

        $shipperUserExists = User::where('username', $shipperData['username'])
            ->orWhere('email', $shipperData['email'])
            ->first();

        if ($shipperUserExists) {
            $hbl->shipper_id = $shipperUserExists->id;
        } else {
            $shipperUser = CreateUser::run($shipperData);
            $hbl->shipper_id = $shipperUser->id;
        }

        $hbl->save();

        $consigneeUserExists = User::where('username', $consigneeData['username'])
            ->first();

        if ($consigneeUserExists) {
            $hbl->consignee_id = $consigneeUserExists->id;
        } else {
            $consigneeUser = CreateUser::run($consigneeData);
            $hbl->consignee_id = $consigneeUser->id;
        }
        $hbl->save();
    }

    /**
     * Handle the HBL "deleted" event.
     */
    public function deleted(HBL $hBL): void
    {
        foreach (static::$cascade_relations as $relation) {
            foreach ($hBL->{$relation}()->get() as $item) {
                $item->delete();
            }
        }
    }

    /**
     * Handle the HBL "restored" event.
     */
    public function restored(HBL $hBL): void
    {
        foreach (static::$cascade_relations as $relation) {
            foreach ($hBL->{$relation}()->withTrashed()->get() as $item) {
                $item->restore();
            }
        }
    }

    public function updated(HBL $hbl): void
    {
        if ($hbl->wasChanged('system_status') && $hbl->system_status === HBL::SYSTEM_STATUS_CASH_RECEIVED) {
            // Send notification email
            $this->notificationMailRepository->sendCashReceivedNotification($hbl);
        }
        if ($hbl->wasChanged('cargo_type') || $hbl->wasChanged('hbl_type') || $hbl->wasChanged('warehouse_id')) {
            $hblPackages = $hbl->packages;
            foreach ($hblPackages as $hblPackage) {
                if (! $hblPackage['package_rule'] && $hblPackage['package_rule'] <= 0) {
                    $rules = GetPriceRulesByCargoModeAndHBLType::run($hbl['cargo_type'], $hbl['hbl_type'], $hbl['warehouse_id']);
                    $data['rules'] = json_encode($rules);
                    UpdateHBLPackageRuleData::run($hblPackage, $data);
                }
            }
        }
    }
}
