<?php

namespace App\Listeners;

use App\Events\PickupCreated;
use App\Notifications\ConfirmPickupNotification;
use Illuminate\Support\Facades\Notification;

class SendPickupCreatedNotification
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
    public function handle(PickupCreated $event): void
    {
        $pickUp = $event->pickUp;
        
        // Send WhatsApp notifications if branch has WhatsApp configured
        if ($pickUp->branch && $pickUp->branch->whatsapp_phone_number_id && $pickUp->whatsapp_number) {
            $whatsapp_number = $pickUp->whatsapp_number;
            Notification::send($whatsapp_number, new ConfirmPickupNotification($pickUp));
        }
    }
}
