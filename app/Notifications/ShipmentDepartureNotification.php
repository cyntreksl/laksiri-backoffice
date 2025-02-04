<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Classes\WhatsAppTemplates\ShipmentDepartureWhatsAppTemplate;
use App\Models\HBL;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShipmentDepartureNotification extends Notification
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
    public function via(object $notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsapp(object $notifiable)
    {
        $shipper = $this->HBL->shipper;
        $branch = $this->HBL->branch;
        $country = $branch->country ?? 'Qatar';

        $template = new ShipmentDepartureWhatsAppTemplate($shipper->name, $this->HBL->hbl, $country);

        return [
            'messaging_product' => 'whatsapp',
            'to' => $notifiable->contact, // TODO: it should be whatsapp number
            'type' => 'template',
            'template' => $template->getTemplate(),
        ];
    }
}
