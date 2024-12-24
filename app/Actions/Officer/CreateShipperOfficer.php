<?php

namespace App\Actions\Officer;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Officer;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateShipperOfficer
{
    use AsAction;

    public function handle(array $data): Officer
    {
        $officer = new Officer();
        $officer->name = $data['name'] ?? null;
        $officer->type = $data['type'];
        $officer->email = $data['email'] ?? null;
        $officer->mobile_number = $data['mobile_number'] ?? null;
        $officer->pp_or_nic_no = $data['pp_or_nic_no'] ?? null;
        $officer->residency_no = $data['residency_no'] ?? null;
        $officer->address = $data['address'] ?? null;
        $officer->description = $data['description'] ?? null;
        $officer->branch_id = GetUserCurrentBranchID::run();
        $officer->save();

        return $officer;
    }
}
