<?php

namespace App\Actions\BranchPrice;

use App\Models\BranchPrice;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdatePriceRule
{
    use AsAction;

    public function handle(array $data, BranchPrice $branchPrice)
    {
        return $branchPrice->update([
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
