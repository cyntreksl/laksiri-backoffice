<?php

namespace App\Repositories;

use App\Actions\Branch\GetBranchByName;
use App\Actions\MHBL\CreateMHBL;
use App\Actions\MHBL\CreateMHBLsHBL;
use App\Interfaces\MHBLRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class MHBLRepository implements MHBLRepositoryInterface
{
    public function storeHBL(array $data)
    {
        $mhbl_data = [
            'branch_id' => session('current_branch_id'),
            'created_by' => Auth::user()->id,
            'consignee_id' => $data['consignee_id'],
            'shipper_id' => $data['shipper_id'],
            'cargo_type' => $data['cargo_type'],
            'grand_volume' => $data['grand_volume'],
            'grand_weight' => $data['grand_weight'],
            'grand_total' => $data['grand_total'],
            'warehouse_id' => GetBranchByName::run($data['warehouse'])->id,
        ];
        $mhbl = CreateMHBL::run($mhbl_data);
        CreateMHBLsHBL::run($mhbl, $data['hbls']);

        return $mhbl;
    }
}
