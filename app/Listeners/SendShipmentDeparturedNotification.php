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
        $whatsapp_number = $hbl->whatsapp_number;

        Notification::send($whatsapp_number, new ShipmentDepartureNotification($hbl));
    }
}
