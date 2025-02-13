<?php

namespace App\Repositories;

use App\Actions\HBL\GetHBLById;
use App\Actions\Setting\GetSettings;
use App\Actions\User\GetUserById;
use App\Interfaces\NotificationMailRepositoryInterface;
use App\Mail\Notification;
use App\Models\HBL;
use App\Models\PickUp;
use Illuminate\Support\Facades\Mail;

class NotificationMailRepository implements NotificationMailRepositoryInterface
{
    protected $settings;

    public function __construct()
    {
        $this->settings = GetSettings::run();
    }

    public function sendPickupCreationNotification(PickUp $pickUp)
    {
        $notification_settings = ! empty($this->settings->notification)
            ? json_decode($this->settings->notification, true)
            : null;
        if ($notification_settings && isset($notification_settings['Email']) && $notification_settings['Email'] === true && $pickUp->email) {
            $email_data = [
                'subject' => 'Booking Confirmation',
                'customer_name' => $pickUp->name,
                'success_message' => 'Thank you for booking with us. Your booking is confirmed. ',
                'detail_message' => 'Booking Reference Number: '.$pickUp->reference.' We will notify you with updates.',
            ];
            Mail::to($pickUp->email)->send(new Notification($email_data));
        }
    }

    public function sendAssignDriverNotification(PickUp $pickUp)
    {
        $notification_settings = ! empty($this->settings->notification)
            ? json_decode($this->settings->notification, true)
            : null;
        $driver = GetUserById::run($pickUp['driver_id']);
        if ($notification_settings && isset($notification_settings['Email']) && $notification_settings['Email'] === true && $pickUp->email) {
            $email_data = [
                'subject' => 'Driver assigned to collect cargo',
                'customer_name' => $pickUp['name'],
                'success_message' => 'A driver has been assigned for your cargo collection. ',
                'detail_message' => 'Driver Name: '.$driver->name.' Contact Number: '.$driver->contact,
            ];
            Mail::to($pickUp['email'])->send(new Notification($email_data));
        }
    }

    public function sendCollectedCargoNotification(PickUp $pickUp)
    {
        $notification_settings = ! empty($this->settings->notification)
            ? json_decode($this->settings->notification, true)
            : null;
        $driver = GetUserById::run($pickUp['driver_id']);
        $hbl = $pickUp->hbl;
        if ($notification_settings && isset($notification_settings['Email']) && $notification_settings['Email'] === true && $pickUp->email) {
            $email_data = [
                'subject' => 'Cargo collected successfully',
                'customer_name' => $pickUp['name'],
                'success_message' => 'Your cargo has been collected successfully.  ',
                'detail_message' => 'HBL Reference Number: '.$pickUp->hbl['hbl_number'].' You can track your cargo here:  ',
                'tracking_link' => 'https://laksiri.world/tracking?hbl='.$pickUp->hbl['hbl_number'],
            ];
            Mail::to($pickUp['email'])->send(new Notification($email_data));
        }
    }

    public function sendCashReceivedNotification(HBL $hbl)
    {
        $notification_settings = ! empty($this->settings->notification)
            ? json_decode($this->settings->notification, true)
            : null;
        if ($notification_settings && isset($notification_settings['Email']) && $notification_settings['Email'] === true && $hbl['email']) {
            $email_data = [
                'subject' => 'Cash Received successfully',
                'customer_name' => $hbl['hbl_name'],
                'success_message' => 'Cash Received successfully.  ',
                'detail_message' => 'HBL Reference Number: '.$hbl['hbl_number'].' You can track your cargo here:  ',
                'tracking_link' => 'https://laksiri.world/tracking?hbl='.$hbl['hbl_number'],
            ];
            Mail::to($hbl['email'])->send(new Notification($email_data));
        }
    }

    public function sendShipmentDepartureNotification(array $data)
    {
        $notification_settings = ! empty($this->settings->notification)
            ? json_decode($this->settings->notification, true)
            : null;

        foreach ($data as $hblNumber) {
            $hbl = GetHBLById::run($hblNumber);
            if ($notification_settings && isset($notification_settings['Email']) && $notification_settings['Email'] === true && $hbl['email']) {
                $email_data = [
                    'subject' => 'Cargo departs Qatar.',
                    'customer_name' => $hbl['hbl_name'],
                    'success_message' => 'Cargo departs Qatar.  ',
                    'detail_message' => 'Your shipment has left Qatar and is en route to Sri Lanka. HBL Reference Number: '.$hbl['hbl_number'].' You can track your cargo here:  ',
                    'tracking_link' => 'https://127.0.0.1:8000/tracking?hbl='.$hbl['hbl_number'],
                ];
                Mail::to($hbl['email'])->send(new Notification($email_data));
            }
        }

    }
}
