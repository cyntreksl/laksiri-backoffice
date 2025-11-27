<?php

namespace App\Listeners;

use App\Actions\Notification\SendPickupAssignedNotificationToDriver;
use App\Events\PickupDriverAssigned;
use App\Notifications\PickupDriverAssignmentNotification;
use Illuminate\Support\Facades\Notification;

class SendPickupDriverAssignedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PickupDriverAssigned $event): void
    {
        $pickUp = $event->pickUp;
        
        // Only send WhatsApp notifications for Qatar branch
        if ($pickUp->branch && strtolower($pickUp->branch->country) === 'qatar') {
            $whatsapp_number = $pickUp->whatsapp_number;
            Notification::send($whatsapp_number, new PickupDriverAssignmentNotification($pickUp));
        }

        SendPickupAssignedNotificationToDriver::run($pickUp);

    }
}
