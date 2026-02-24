<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Classes\WhatsAppTemplates\PickupConfirmationWhatsAppTemplate;
use App\Models\PickUp;
use Illuminate\Notifications\Notification;

class ConfirmPickupNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(private PickUp $pickUp)
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
        $branch = $this->pickUp->branch;
        $template = new PickupConfirmationWhatsAppTemplate($this->pickUp->name, $this->pickUp->reference);

        return [
            'messaging_product' => 'whatsapp',
            'to' => $notifiable,
            'type' => 'template',
            'template' => $template->getTemplate(),
            'phone_number_id' => $branch->whatsapp_phone_number_id,
        ];
    }
}
