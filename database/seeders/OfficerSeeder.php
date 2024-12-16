<?php

namespace Database\Seeders;

use App\Models\Officer;
use Illuminate\Database\Seeder;

class OfficerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $officers = [
            [
                'type' => 'shipper',
                'name' => 'John Doe',
                'email' => 'johndoe@example.com',
                'mobile_number' => '1234567890',
                'pp_or_nic_no' => 'NIC123456',
                'residency_no' => 'RES123',
                'address' => '123 Main Street, City A',
            ],
            [
                'type' => 'consignee',
                'name' => 'Jane Smith',
                'email' => 'janesmith@example.com',
                'mobile_number' => '0987654321',
                'pp_or_nic_no' => 'NIC654321',
                'residency_no' => 'RES456',
                'address' => '456 Elm Street, City B',
            ],
            [
                'type' => 'shipper',
                'name' => 'Alice Brown',
                'email' => 'alicebrown@example.com',
                'mobile_number' => '1122334455',
                'pp_or_nic_no' => 'NIC789012',
                'residency_no' => 'RES789',
                'address' => '789 Oak Street, City C',
            ],
            [
                'type' => 'consignee',
                'name' => 'Bob White',
                'email' => 'bobwhite@example.com',
                'mobile_number' => '6677889900',
                'pp_or_nic_no' => 'NIC345678',
                'residency_no' => 'RES101',
                'address' => '101 Pine Street, City D',
            ],
        ];

        foreach ($officers as $officer) {
            Officer::create($officer);
        }
    }
}
