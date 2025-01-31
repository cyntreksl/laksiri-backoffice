<?php

namespace App\Actions\HBL;

use App\Actions\User\GetUserCurrentBranch;
use App\Enum\BranchType;
use App\Enum\WarehouseType;
use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLByCargoTypeWithDestinationUnloadedPackages
{
    use AsAction;

    public function handle(array $data)
    {
        dd($data);
        if (GetUserCurrentBranch::run()['branchType'] === BranchType::DESTINATION->value) {
            if (auth()->user()->hasRole('boned area')) {
                if (GetUserCurrentBranch::run()['branchName'] == 'Colombo') {
                    $query = HBL::whereDoesntHave('mhbl');

                    if (isset($data['cargoType'])) {
                        $query->where('cargo_type', $data['cargoType']);
                    }

                    if (isset($data['hblType'])) {
                        $query->where('hbl_type', $data['hblType']);
                    }

                    if (isset($data['warehouse'])) {
                        $query->where('warehouse', $data['warehouse']);
                    } else {
                        $query->where('warehouse', WarehouseType::NINTAVUR->value);
                    }

                    $query->where('is_hold', false)
                        ->latest()
                        ->with(['packages' => function ($query) {
                            $query->where('is_loaded', true);
                        }]);

                    return $query->get();
                }
            }
        } else {
            $query = HBL::whereDoesntHave('mhbl');

            if (isset($data['cargoType'])) {
                $query->where('cargo_type', $data['cargoType']);
            }

            if (isset($data['hblType'])) {
                $query->where('hbl_type', $data['hblType']);
            }

            if (isset($data['warehouse'])) {
                $query->where('warehouse', $data['warehouse']);
            }

            $query->where('is_hold', false)
                ->whereIn('system_status', [HBL::SYSTEM_STATUS_CASH_RECEIVED, 4.2])
                ->latest()
                ->with(['packages' => function ($query) {
                    $query->where('is_loaded', false);
                }]);

            return $query->get();
        }
    }
}
