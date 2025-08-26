<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchUser;
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
                    'name' => 'Administrator',
                    'username' => 'admin',
                    'password' => Hash::make('password'),
                ]);

                $user->assignRole('admin');
                $this->assignBranchToSuperAdmin($user);

                $this->command->info('Here is your super administrator details to login:');
                $this->command->warn($user->username);
                $this->command->warn('Password is "password"');
            });

        }

        $superAdmin = User::where('username', 'super-admin')->first();

        if ($superAdmin === null) {
            // Disable observer events during seeding
            User::withoutEvents(function () {
                $superAdmin = User::create([
                    'primary_branch_id' => 1,
                    'name' => 'Super Administrator',
                    'username' => 'super-admin',
                    'password' => Hash::make('password'),
                ]);

                $superAdmin->assignRole('super-admin');
                $this->assignBranchToSuperAdmin($superAdmin);

                $this->command->info('Here is your super administrator details to login:');
                $this->command->warn($superAdmin->username);
                $this->command->warn('Password is "password"');
            });

        }
    }

    protected function assignBranchToSuperAdmin(User $user): void
    {
        $branchIds = Branch::pluck('id')->toArray();
        foreach ($branchIds as $branchId) {
            BranchUser::updateOrCreate(['user_id' => $user->id, 'branch_id' => $branchId]);
        }
    }
}
