<?php

namespace App\Listeners;

use App\Events\PickupCollected;
use App\Notifications\CargoCollectedNotification;
use Illuminate\Support\Facades\Notification;

class SendPickupCollectedNotification
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
    public function handle(PickupCollected $event): void
    {
        $hbl = $event->HBL;
        $shipper = $hbl->shipper;

        //        Notification::send($shipper, new CargoCollectedNotification($hbl));
    }
}
