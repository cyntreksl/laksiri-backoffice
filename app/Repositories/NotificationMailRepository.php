<?php

namespace App\Repositories;

use App\Actions\PickUps\GetPickupByIds;
use App\Actions\Setting\GetSettings;
use App\Actions\User\GetUserById;
use App\Interfaces\NotificationMailRepositoryInterface;
use App\Mail\Notification;
use Illuminate\Support\Facades\Mail;

class NotificationMailRepository implements NotificationMailRepositoryInterface
{
    protected $settings;

    public function __construct()
    {
        $this->settings = GetSettings::run();
    }

    public function sendAssignDriverNotification(array $data)
    {
        $notification_settings = json_decode($this->settings->notification, true);
        $driver = GetUserById::run($data['driver_id']);
        $pickups = GetPickupByIds::run(array_column($data['job_ids'], 'id'));
        if (isset($notification_settings['Email']) && $notification_settings['Email'] === true) {
            foreach ($pickups as $pickup) {
                $email_data = [
                    'subject' => 'Driver assigned to collect cargo',
                    'customer_name' => $pickup['name'],
                    'success_message' => 'A driver has been assigned for your cargo collection. ',
                    'detail_message' => 'Driver Name: '.$driver->name.' Contact Number: '.$driver->contact,
                ];
                Mail::to($pickup['email'])->send(new Notification($email_data));
            }
        }
    }
}
