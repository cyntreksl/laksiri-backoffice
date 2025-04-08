<?php

namespace App\Actions\Notification;

use App\Models\DeviceToken;
use App\Models\PickUp;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class SendPickupAssignedNotificationToDriver
{
    use AsAction;

    public function handle(PickUp $pickUp)
    {
        $driver_id = $pickUp->driver_id;
        $reference_number = $pickUp->reference;

        $message = "You have been assigned a new pickup with reference number: $reference_number";
        $title = 'New Pickup Assigned';

        $expoToken = DeviceToken::where('user_id', $driver_id)->first();

        if ($expoToken instanceof DeviceToken) {
            $expoToken = $expoToken->token;
            if (! empty($expoToken)) {
                SendNotification::run($expoToken, $title, $message);
            } else {
                Log::info("No expo token found for driver with ID: $driver_id");
            }
        } else {
            Log::info("No device token found for driver with ID: $driver_id");
        }

    }
}
