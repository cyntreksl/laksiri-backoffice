<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
//            NoteTypeSeeder::class,
//            BranchSeeder::class,
            RolePermissionSeeder::class,
//            UserSeeder::class,
        ]);
    }
}
