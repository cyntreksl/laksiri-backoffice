<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLByCargoTypeWithDestinationUnloadedPackages
{
    use AsAction;

    public function handle(array $data)
    {
        $query = HBL::query();

        if (isset($data['cargoType'])) {
            $query->where('cargo_type', $data['cargoType']);
        }

        if (isset($data['hblType'])) {
            $query->where('hbl_type', $data['hblType']);
        }

        $query->with(['packages' => function ($query) {
            $query->where('is_unloaded', true)
                ->where('is_de_loaded', false)
                ->whereNotNull('current_warehouse')
                ->whereHas('hbl', function ($hblQuery) {
                    $hblQuery->whereColumn('hbl_packages.current_warehouse', '!=', 'hbl.warehouse_id')
                        ->whereNotIn('hbl_packages.current_warehouse', function ($subQuery) {
                            $subQuery->select('id')
                                ->from('branches')
                                ->whereColumn('branches.name', 'hbl.warehouse');
                        });
                });
        }]);

        return $query->get();
    }
}
