<?php

namespace App\Actions\Zone;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Zone;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateZone
{
    use AsAction;
    public function handle(array $data): Zone
    {
        return Zone::firstOrCreate(
            [
                'name' => $data['name'],
                'branch_id' => GetUserCurrentBranchID::run(),
            ],
            [
                'pickup_area' => $data['pickup_area'] ?? null,
            ]
        );
    }

}
