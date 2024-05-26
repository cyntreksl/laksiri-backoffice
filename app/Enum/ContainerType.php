<?php

namespace App\Enum;

enum ContainerType: string
{
    // Sea Cargo Containers
    case TwentyFTGeneral = '20FT General';
    case TwentyFTHighCube = '20FT High Cube';
    case FortyFTGeneral = '40FT General';
    case FortyFTHighCube = '40FT High Cube';

    // Air Cargo Containers
    case AirCargoULD1 = 'Air Cargo ULD 1';
    case AirCargoULD2 = 'Air Cargo ULD 2';

    public function dimensions(): array
    {
        return match($this) {
            // Sea Cargo Dimensions
            self::TwentyFTGeneral => ['L' => 5.89, 'W' => 2.35, 'H' => 2.36],
            self::TwentyFTHighCube => ['L' => 5.89, 'W' => 2.35, 'H' => 2.69],
            self::FortyFTGeneral => ['L' => 12.05, 'W' => 2.35, 'H' => 2.36],
            self::FortyFTHighCube => ['L' => 12.05, 'W' => 2.35, 'H' => 2.69],

            // Air Cargo Dimensions (example values)
            self::AirCargoULD1 => ['L' => 3.17, 'W' => 2.44, 'H' => 1.60],
            self::AirCargoULD2 => ['L' => 3.17, 'W' => 2.44, 'H' => 2.44],
        };
    }

    public function cubicCapacity(): float
    {
        return match($this) {
            // Sea Cargo Capacities
            self::TwentyFTGeneral => 33.0,
            self::TwentyFTHighCube => 37.0,
            self::FortyFTGeneral => 66.0,
            self::FortyFTHighCube => 76.0,

            // Air Cargo Capacities (example values)
            self::AirCargoULD1 => 12.4,
            self::AirCargoULD2 => 19.5,
        };
    }

    public function cargoWeight(): float
    {
        return match($this) {
            // Sea Cargo Weights
            self::TwentyFTGeneral, self::TwentyFTHighCube => 21700.0,
            self::FortyFTGeneral, self::FortyFTHighCube => 26500.0,

            // Air Cargo Weights (example values)
            self::AirCargoULD1 => 5000.0,
            self::AirCargoULD2 => 6800.0,
        };
    }

    public function volumetricWeight(float $divisor = 1_000_000): float
    {
        $dimensions = $this->dimensions();
        $volume = $dimensions['L'] * $dimensions['W'] * $dimensions['H'];
        return $volume * 1000 / $divisor; // Convert volume in cubic meters to kilograms
    }

    public static function getDropdownOptions(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public static function getSeaCargoOptions(): array
    {
        return array_filter(self::cases(), fn($case) => in_array($case, [
            self::TwentyFTGeneral,
            self::TwentyFTHighCube,
            self::FortyFTGeneral,
            self::FortyFTHighCube,
        ]));
    }

    public static function getAirCargoOptions(): array
    {
        return array_filter(self::cases(), fn($case) => in_array($case, [
            self::AirCargoULD1,
            self::AirCargoULD2,
        ]));
    }
}
