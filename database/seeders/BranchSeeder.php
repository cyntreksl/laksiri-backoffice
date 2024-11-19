<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'Riyadh',
                'slug' => 'riyadh',
                'type' => 'Departure',
                'currency_name' => 'Saudi Arabian Riyal',
                'currency_symbol' => 'SA',
                'cargo_modes' => json_encode(['Sea Cargo', 'Air Cargo']),
                'delivery_types' => json_encode(['UBP', 'Gift', 'Door to Door']),
                'package_types' => json_encode(['Departure', 'Destination']),
            ],
            [
                'name' => 'Colombo',
                'slug' => 'colombo',
                'type' => 'Destination',
                'currency_name' => 'Sri Lankan Rupee',
                'currency_symbol' => 'LKR',
                'cargo_modes' => json_encode(['Air Cargo', 'Sea Cargo', 'Door to Door']),
                'delivery_types' => json_encode(['UBP', 'Gift', 'Door to Door']),
                'package_types' => json_encode(['Destination']),
            ],
            [
                'name' => 'Dubai',
                'slug' => 'dubai',
                'type' => 'Departure',
                'currency_name' => 'United Arab Emirates Dirham',
                'currency_symbol' => 'AED',
                'cargo_modes' => json_encode(['Air Cargo', 'Door to Door']),
                'delivery_types' => json_encode(['UBP', 'Door to Door', 'Gift']),
                'package_types' => json_encode(['Departure', 'Destination']),
            ],
            [
                'name' => 'Kuwait',
                'slug' => 'kuwait',
                'type' => 'Departure',
                'currency_name' => 'Kuwaiti Dinar',
                'currency_symbol' => 'KD',
                'cargo_modes' => json_encode(['Sea Cargo', 'Air Cargo', 'Door to Door']),
                'delivery_types' => json_encode(['UBP', 'Gift', 'Door to Door']),
                'package_types' => json_encode(['Departure', 'Destination']),
            ],
            [
                'name' => 'Qatar',
                'slug' => 'qatar',
                'type' => 'Departure',
                'currency_name' => 'Qatari riyal',
                'currency_symbol' => 'QAR',
                'cargo_modes' => json_encode(['Sea Cargo', 'Air Cargo']),
                'delivery_types' => json_encode(['Gift', 'Door to Door', 'UPB']),
                'package_types' => json_encode(['Departure']),
            ],
            [
                'name' => 'Malaysia',
                'slug' => 'malaysia',
                'type' => 'Departure',
                'currency_name' => 'Malaysian Ringgit',
                'currency_symbol' => 'MYR',
                'cargo_modes' => json_encode(['Sea Cargo', 'Air Cargo']),
                'delivery_types' => json_encode(['UBP', 'Gift', 'Door to Door']),
                'package_types' => json_encode(['Departure', 'Destination']),
            ],
            [
                'name' => 'Nintavur',
                'slug' => 'nintavur',
                'type' => 'Destination',
                'currency_name' => 'Dollers',
                'currency_symbol' => 'USD',
                'cargo_modes' => json_encode(['Sea Cargo', 'Air Cargo']),
                'delivery_types' => json_encode(['UBP', 'Door to Door', 'Gift']),
                'package_types' => json_encode(['Destination']),
            ],
            [
                'name' => 'London',
                'slug' => 'london',
                'type' => 'Departure',
                'currency_name' => 'GBP',
                'currency_symbol' => 'GBP',
                'cargo_modes' => json_encode(['Sea Cargo', 'Air Cargo']),
                'delivery_types' => json_encode(['Gift', 'UBP', 'Door to Door']),
                'package_types' => json_encode(['Departure']),
            ],
        ];

        if (Branch::count() === 0) {
            Branch::insert($data);
        }

        foreach ($data as $branch) {
            Branch::updateOrCreate(
                ['slug' => $branch['slug']],
                $branch
            );
        }
    }
}
