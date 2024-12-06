<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('database\sql\countries.sql');
        $sql = file_get_contents($filePath);

        // Execute the SQL queries
        DB::unprepared($sql);

    }
}
