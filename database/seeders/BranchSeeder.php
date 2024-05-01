<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Riyadh', 'slug' => 'riyadh'],
            ['name' => 'Sri Lanka', 'slug' => 'sri-lanka'],
            ['name' => 'Dubai', 'slug' => 'dubai'],
            ['name' => 'Kuwait', 'slug' => 'kuwait'],
        ];

        if (Branch::count() === 0) {
            Branch::insert($data);
        }
    }
}
