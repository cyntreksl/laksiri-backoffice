<?php

namespace App\Observers;

use App\Actions\User\CreateUser;
use App\Actions\User\GetUserCurrentBranchID;
use App\Interfaces\NotificationMailRepositoryInterface;
use App\Models\PickUp;
use App\Models\User;
use Spatie\Permission\Models\Role;

class PickupObserver
{
    protected $notificationMailRepository;

    public function __construct(NotificationMailRepositoryInterface $notificationMailRepository)
    {
        $this->notificationMailRepository = $notificationMailRepository;
    }

    /**
     * Handle the PickUp "created" event.
     */
    public function created(PickUp $pickup): void
    {
        Role::updateOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        $data = [
            'role' => 'customer',
            'name' => $pickup->name,
            'email' => $pickup->email,
            'username' => $pickup->contact_number,
            'contact' => $pickup->contact_number,
            'password' => bcrypt('password'),
            'is_shipper' => true,
            'primary_branch_id' => GetUserCurrentBranchID::run(),
        ];

        $userExists = User::where('username', $data['username'])
            ->orWhere('email', $pickup->email)
            ->first();

        if ($userExists) {
            $pickup->shipper_id = $userExists->id;
        } else {
            $user = CreateUser::run($data);
            $pickup->shipper_id = $user->id;
        }
        $pickup->save();
    }

    public function updated(PickUp $pickup): void
    {
        if ($pickup->wasChanged('driver_id')) {
            // Send notification email
            $this->notificationMailRepository->sendAssignDriverNotification($pickup);
        }
    }
}
