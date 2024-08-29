<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Riyadh', 'slug' => 'riyadh', 'type' => 'departure'],
            ['name' => 'Sri Lanka', 'slug' => 'sri-lanka', 'type' => 'destination'],
            ['name' => 'Dubai', 'slug' => 'dubai', 'type' => 'departure'],
            ['name' => 'Kuwait', 'slug' => 'kuwait', 'type' => 'departure'],
        ];

        if (Branch::count() === 0) {
            Branch::insert($data);
        }
    }
}
