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
        $shipper = $hbl->shipper;

        Notification::send($shipper, new ShipmentDepartureNotification($hbl));
    }
}
