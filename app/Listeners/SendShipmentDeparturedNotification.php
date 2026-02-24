<?php

namespace App\Listeners;

use App\Events\ShipmentDepartured;
use App\Notifications\ShipmentDepartureNotification;
use Illuminate\Support\Facades\Notification;

class SendShipmentDeparturedNotification
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
    public function handle(ShipmentDepartured $event): void
    {
        $hbl = $event->HBL;
        
        // Send WhatsApp notifications if branch has WhatsApp configured
        if ($hbl->branch && $hbl->branch->whatsapp_phone_number_id && $hbl->whatsapp_number) {
            $whatsapp_number = $hbl->whatsapp_number;
            Notification::send($whatsapp_number, new ShipmentDepartureNotification($hbl));
        }
    }
}
