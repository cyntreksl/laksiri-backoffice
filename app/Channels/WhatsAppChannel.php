<?php

namespace App\Channels;

use App\Services\WhatsAppService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    public function __construct(private WhatsAppService $whatAppService) {}

    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toWhatsApp($notifiable);
        try {
            $this->whatAppService->sendMessageByTemplate($message);
        } catch (\Exception $e) {
            Log::error('WhatsApp message exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
        }
    }
}
