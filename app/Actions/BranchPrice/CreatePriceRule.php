<?php

namespace App\Actions\BranchPrice;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\BranchPrice;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePriceRule
{
    use AsAction;

    public function handle(array $data): BranchPrice
    {
        return BranchPrice::create([
            'branch_id' => GetUserCurrentBranchID::run(),
            'destination_branch_id' => $data['destination_branch_id'],
            'cargo_mode' => $data['cargo_mode'],
            'hbl_type' => $data['hbl_type'],
            'price_mode' => $data['price_mode'],
            'condition' => $data['condition'],
            'true_action' => $data['true_action'],
            'false_action' => $data['false_action'],
            'bill_price' => $data['bill_price'],
            'bill_vat' => $data['bill_vat'],
            'volume_charges' => $data['volume_charges'],
            'per_package_charges' => $data['per_package_charges'],
            'is_editable' => (bool) $data['is_editable'],
        ]);
    }
}
