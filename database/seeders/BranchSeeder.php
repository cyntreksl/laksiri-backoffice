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
                'branch_code' => 'RY',
                'type' => 'Departure',
                'currency_name' => 'Saudi Arabian Riyal',
                'currency_symbol' => 'SA',
                'country_code' => '+966',
                'cargo_modes' => json_encode(['Sea Cargo', 'Air Cargo']),
                'delivery_types' => json_encode(['UBP', 'Gift', 'Door to Door']),
                'package_types' => json_encode(['Departure', 'Destination']),
            ],
            [
                'name' => 'Colombo',
                'slug' => 'colombo',
                'branch_code' => 'CO',
                'type' => 'Destination',
                'currency_name' => 'Sri Lankan Rupee',
                'currency_symbol' => 'LKR',
                'country_code' => '+94',
                'cargo_modes' => json_encode(['Air Cargo', 'Sea Cargo', 'Door to Door']),
                'delivery_types' => json_encode(['UBP', 'Gift', 'Door to Door']),
                'package_types' => json_encode(['Destination']),
            ],
            [
                'name' => 'Dubai',
                'slug' => 'dubai',
                'branch_code' => 'DB',
                'type' => 'Departure',
                'currency_name' => 'United Arab Emirates Dirham',
                'currency_symbol' => 'AED',
                'country_code' => '+971',
                'cargo_modes' => json_encode(['Air Cargo', 'Door to Door']),
                'delivery_types' => json_encode(['UBP', 'Door to Door', 'Gift']),
                'package_types' => json_encode(['Departure', 'Destination']),
            ],
            [
                'name' => 'Kuwait',
                'slug' => 'kuwait',
                'branch_code' => 'KW',
                'type' => 'Departure',
                'currency_name' => 'Kuwaiti Dinar',
                'currency_symbol' => 'KD',
                'country_code' => '+965',
                'cargo_modes' => json_encode(['Sea Cargo', 'Air Cargo', 'Door to Door']),
                'delivery_types' => json_encode(['UBP', 'Gift', 'Door to Door']),
                'package_types' => json_encode(['Departure', 'Destination']),
            ],
            [
                'name' => 'Qatar',
                'slug' => 'qatar',
                'branch_code' => 'QT',
                'type' => 'Departure',
                'currency_name' => 'Qatari riyal',
                'currency_symbol' => 'QAR',
                'country_code' => '+974',
                'cargo_modes' => json_encode(['Sea Cargo', 'Air Cargo']),
                'delivery_types' => json_encode(['Gift', 'Door to Door', 'UPB']),
                'package_types' => json_encode(['Departure']),
            ],
            [
                'name' => 'Malaysia',
                'slug' => 'malaysia',
                'branch_code' => 'ML',
                'type' => 'Departure',
                'currency_name' => 'Malaysian Ringgit',
                'currency_symbol' => 'MYR',
                'country_code' => '+60',
                'cargo_modes' => json_encode(['Sea Cargo', 'Air Cargo']),
                'delivery_types' => json_encode(['UBP', 'Gift', 'Door to Door']),
                'package_types' => json_encode(['Departure', 'Destination']),
            ],
            [
                'name' => 'Nintavur',
                'slug' => 'nintavur',
                'branch_code' => 'NT',
                'type' => 'Destination',
                'currency_name' => 'Dollers',
                'currency_symbol' => 'USD',
                'country_code' => '+94',
                'cargo_modes' => json_encode(['Sea Cargo', 'Air Cargo']),
                'delivery_types' => json_encode(['UBP', 'Door to Door', 'Gift']),
                'package_types' => json_encode(['Destination']),
            ],
            [
                'name' => 'London',
                'slug' => 'london',
                'branch_code' => 'LD',
                'type' => 'Departure',
                'currency_name' => 'GBP',
                'currency_symbol' => 'GBP',
                'country_code' => '+44',
                'cargo_modes' => json_encode(['Sea Cargo', 'Air Cargo']),
                'delivery_types' => json_encode(['Gift', 'UBP', 'Door to Door']),
                'package_types' => json_encode(['Departure']),
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'branch_code' => 'OTR',
                'type' => 'Destination',
                'currency_name' => 'Dollers',
                'currency_symbol' => 'USD',
                'country_code' => '+1',
                'cargo_modes' => json_encode(['Sea Cargo', 'Air Cargo']),
                'delivery_types' => json_encode(['UBP', 'Door to Door', 'Gift']),
                'package_types' => json_encode(['Destination']),
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
