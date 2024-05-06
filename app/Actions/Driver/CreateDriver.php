<?php

namespace App\Actions\Driver;

use App\Enum\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\Models\Role;

class CreateDriver
{
    use AsAction;

    public function handle(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'username' => $data['name'],
            'password' => Hash::make($data['password']),
            'working_hours_start' => $data['working_hours_start'],
            'working_hours_end' => $data['working_hours_end'],
            'preferred_zone' => $data['preferred_zone'],
            'contact' => $data['contact'],
            'status' => UserStatus::INVITED->value,
            'primary_branch_id' => auth()->user()->primary_branch_id,
        ]);

        $driver_role = Role::where('name', 'driver')->first();

        if ($driver_role) {
            // assign role
            $user->assignRole($driver_role);
        }

        return $user;
    }
}
