<?php

namespace App\Channels;

use App\Services\WhatsAppService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toWhatsApp($notifiable);
        
        // Get branch-specific phone number ID if available
        $phoneNumberId = $message['phone_number_id'] ?? null;
        unset($message['phone_number_id']); // Remove from message payload
        
        try {
            $whatsAppService = new WhatsAppService($phoneNumberId);
            $whatsAppService->sendMessageByTemplate($message);
        } catch (\Exception $e) {
            Log::error('WhatsApp message exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
        }
    }
}
