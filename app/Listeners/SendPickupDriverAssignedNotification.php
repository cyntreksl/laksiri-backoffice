<?php

namespace App\Listeners;

use App\Events\PickupDriverAssigned;
use App\Models\User;
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
        $contact_number = $pickUp->contact_number;

        $user = User::where('contact', $contact_number)->first();
        //        Notification::send($user, new PickupDriverAssignmentNotification($pickUp));
    }
}
