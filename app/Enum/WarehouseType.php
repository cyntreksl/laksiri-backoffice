<?php

namespace App\Enum;

enum WarehouseType: string
{
    case COLOMBO = 'COLOMBO';

    case NINTAVUR = 'NINTAVUR';

    public static function getWarehouseOptions(): array
    {
        return array_filter(self::cases(), fn ($case) => in_array($case, [
            self::COLOMBO,
            self::NINTAVUR,
        ]));
    }
}
