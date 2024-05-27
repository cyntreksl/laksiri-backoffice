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
            'price_mode' => $data['price_mode'],
            'condition' => $data['condition'],
            'true_action' => $data['true_action'],
            'false_action' => $data['false_action'],
            'is_editable' => (bool) $data['is_editable'],
        ]);
    }
}
