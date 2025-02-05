<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiUrl;

    protected string $phoneNumberId;

    protected string $accessToken;

    public function __construct()
    {
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->apiUrl = sprintf('https://graph.facebook.com/v21.0/%s/messages', $this->phoneNumberId);
        $this->accessToken = config('services.whatsapp.access_token');

    }

    public function isWhatsAppEnabled()
    {
        return config('services.whatsapp.enabled');
    }

    public function isWhatsAppTestMode()
    {
        return config('services.whatsapp.test_receiver_phone_number');
    }

    public function sendMessageByTemplate(array $body)
    {
        if (! $this->isWhatsAppEnabled()) {
            return false;
        }

        try {
            if ($this->isWhatsAppTestMode()) {
                $body['to'] = config('services.whatsapp.test_receiver_phone_number');
            }

            $response = Http::withToken($this->accessToken)
                ->post($this->apiUrl, $body);

            if ($response->successful()) {
                return $response->json();
            } else {
                $error = $response->json();

                Log::error('WhatsApp API Error', [
                    'error' => $error,
                    'status_code' => $response->status(),
                    'message' => $response->body(),
                ]);

                return $error;
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp message exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return $e->getMessage();
        }
    }
}
