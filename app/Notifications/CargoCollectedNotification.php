<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Classes\WhatsAppTemplates\CargoCollectedWhatsAppTemplate;
use App\Models\HBL;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CargoCollectedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private HBL $HBL)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(string $notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsapp(string $notifiable)
    {
        $branch = $this->HBL->branch;
        $template = new CargoCollectedWhatsAppTemplate($this->HBL->hbl_name, $this->HBL->hbl_number);

        return [
            'messaging_product' => 'whatsapp',
            'to' => $notifiable,
            'type' => 'template',
            'template' => $template->getTemplate(),
            'phone_number_id' => $branch->whatsapp_phone_number_id,
        ];
    }
}
