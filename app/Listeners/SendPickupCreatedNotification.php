<?php

namespace App\Listeners;

use App\Events\PickupCreated;
use App\Models\User;
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
        $contact_number = $pickUp->contact_number;

        $user = User::where('contact', $contact_number)->first();

        Notification::send($user, new ConfirmPickupNotification($pickUp));
    }
}
