<?php

namespace App\Actions\SpecialDOCharge;

use App\Models\SpecialDOCharge;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateSpecialDOCharge
{
    use AsAction;

    public function handle(array $data)
    {
        foreach ($data['priceRules'] as $priceRule) {
            // Check if a record already exists
            $existingCharge = SpecialDOCharge::where('agent_id', $data['agent_id'] ?? null)
                ->where('condition', '>'.$priceRule['condition'])
                ->where('collected', $priceRule['collected'] ?? null)
                ->where('package_type', $priceRule['package_type'] ?? null)
                ->first();

            // If it exists, delete it
            if ($existingCharge) {
                $existingCharge->delete();
            }

            // Create and return new SpecialDOCharge
            SpecialDOCharge::create([
                'branch_id' => session('current_branch_id'),
                'agent_id' => $data['agent_id'],
                'hbl_type' => $data['hbl_type'],
                'cargo_type' => $data['cargo_type'],
                'condition' => '>'.$priceRule['condition'],
                'collected' => $priceRule['collected'],
                'quantity_basis' => 1,
                'package_type' => $priceRule['package_type'],
                'charge' => $priceRule['charge'],
                'created_by' => Auth::user()->id,
            ]);
        }
    }
}
