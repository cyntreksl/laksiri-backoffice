<?php

namespace App\Enum;

enum NotificationType: string
{
    case EMAIL = 'Email';

    case WHATSAPP = 'WhatsApp';

    public static function getNotificationTypeOptions(): array
    {
        return array_filter(self::cases(), fn ($case) => in_array($case, [
            self::EMAIL,
            self::WHATSAPP,
        ]));
    }
}
