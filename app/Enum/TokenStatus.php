<?php

namespace App\Enum;

enum TokenStatus: string
{
    case ONGOING = 'ONGOING';
    case COMPLETED = 'COMPLETED';
    case DUE = 'DUE';
    case CANCELLED = 'CANCELLED';

    /**
     * Get the color class for the status badge
     */
    public function getColor(): string
    {
        return match($this) {
            self::COMPLETED => 'success',
            self::DUE => 'danger',
            self::ONGOING => 'info',
            self::CANCELLED => 'secondary',
        };
    }

    /**
     * Get the display label for the status
     */
    public function getLabel(): string
    {
        return match($this) {
            self::COMPLETED => 'Completed',
            self::DUE => 'Due',
            self::ONGOING => 'Ongoing',
            self::CANCELLED => 'Cancelled',
        };
    }
}
