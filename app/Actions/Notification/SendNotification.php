<?php

namespace App\Actions\Notification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class SendNotification
{
    use AsAction;

    public function handle(string $expoToken, string $title, string $body)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://exp.host/--/api/v2/push/send', [
                'to' => $expoToken,
                'title' => $title,
                'body' => $body,
            ]);

            return $response->json();
        } catch (\Exception $exception) {
            Log::error('Error sending notification: '.$exception->getMessage());

            return null;
        }

    }
}
