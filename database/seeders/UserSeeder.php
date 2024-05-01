<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('username', 'admin')->first();

        if ($user === null) {
            // Disable observer events during seeding
            User::withoutEvents(function () {
                $user = User::create([
                    'primary_branch_id' => 1,
                    'name' => 'Super Administrator',
                    'username' => 'admin',
                    'password' => Hash::make('password'),
                ]);

                $user->assignRole('admin');

                $this->command->info('Here is your super administrator details to login:');
                $this->command->warn($user->username);
                $this->command->warn('Password is "password"');
            });

        }
    }
}
