<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Riyadh', 'slug' => 'riyadh', 'type' => 'Departure'],
            ['name' => 'Colombo', 'slug' => 'colombo', 'type' => 'Destination'],
            ['name' => 'Dubai', 'slug' => 'dubai', 'type' => 'Departure'],
            ['name' => 'Kuwait', 'slug' => 'kuwait', 'type' => 'Departure'],
            ['name' => 'Nintaur', 'slug' => 'nintaur', 'type' => 'Destination'],
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
