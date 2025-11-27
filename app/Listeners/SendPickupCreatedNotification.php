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
        
        // Only send WhatsApp notifications for Qatar branch
        if ($pickUp->branch && strtolower($pickUp->branch->country) === 'qatar') {
            $whatsapp_number = $pickUp->whatsapp_number;
            Notification::send($whatsapp_number, new ConfirmPickupNotification($pickUp));
        }
    }
}
