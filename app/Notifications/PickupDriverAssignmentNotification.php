<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Classes\WhatsAppTemplates\DriverAssignmentForCargoCollectWhatsAppTemplate;
use App\Models\PickUp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PickupDriverAssignmentNotification extends Notification
{
    use Queueable;

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
    public function via(object $notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsapp(object $notifiable)
    {
        $driver = $this->pickUp->driver;
        $template = new DriverAssignmentForCargoCollectWhatsAppTemplate($this->pickUp->name, $driver->name, $driver->contact);

        return [
            'messaging_product' => 'whatsapp',
            'to' => $notifiable->contact, // TODO: it should be whatsapp number
            'type' => 'template',
            'template' => $template->getTemplate(),
        ];
    }
}
