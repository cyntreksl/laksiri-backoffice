<?php

namespace App\Actions\BranchPrice;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\BranchPrice;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePriceRule
{
    use AsAction;

    public function handle(array $data)
    {
        foreach ($data['priceRules'] as $priceRule) {
            $branchPrice = new BranchPrice();

            $branchPrice->branch_id = GetUserCurrentBranchID::run();
            $branchPrice->destination_branch_id = $data['destination_branch_id'];
            $branchPrice->cargo_mode = $data['cargo_mode'];
            $branchPrice->hbl_type = $data['hbl_type'];
            $branchPrice->price_mode = $data['price_mode'];
            $branchPrice->condition = $priceRule['condition'];
            $branchPrice->true_action = $priceRule['true_action'];
            $branchPrice->false_action = $priceRule['false_action'];
            $branchPrice->bill_price = $priceRule['bill_price'];
            $branchPrice->bill_vat = $priceRule['bill_vat'];
            $branchPrice->volume_charges = $priceRule['volume_charges'];
            $branchPrice->per_package_charges = $priceRule['per_package_charges'];
            $branchPrice->is_editable = (bool) $priceRule['is_editable'];

            // Save the branch price to the database
            $branchPrice->save();
        }

    }
}
