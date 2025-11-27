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

        // Only send WhatsApp notifications for Qatar branch
        if ($hbl->branch && strtolower($hbl->branch->country) === 'qatar') {
            Notification::send($hbl->whatsapp_number, new CargoCollectedNotification($hbl));
        }
    }
}
