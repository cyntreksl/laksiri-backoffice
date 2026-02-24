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
    public function via(string $notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsapp(string $notifiable)
    {
        $driver = $this->pickUp->driver;
        $branch = $this->pickUp->branch;
        $template = new DriverAssignmentForCargoCollectWhatsAppTemplate($this->pickUp->name, $driver->name, $driver->contact);

        return [
            'messaging_product' => 'whatsapp',
            'to' => $notifiable,
            'type' => 'template',
            'template' => $template->getTemplate(),
            'phone_number_id' => $branch->whatsapp_phone_number_id,
        ];
    }
}
