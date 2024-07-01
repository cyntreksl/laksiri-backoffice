<?php

namespace App\Actions\Zone;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Area;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateZoneArea
{
    use AsAction;

    public function handle(string $data, Zone $zone)
    {
        try {
            DB::beginTransaction();
            $areas = array_map(function ($item) {
                return ['name' => $item];
            }, explode(',', $data));

            foreach ($areas as $areaData) {
                $area = $this->createZoneArea($areaData);
                $zone->areas()->syncWithoutDetaching($area->id);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    }

    private function createZoneArea(array $areaData)
    {
        return Area::firstOrCreate(
            [
                'name' => $areaData['name'],
                'branch_id' => GetUserCurrentBranchID::run(),
            ]
        );
    }
}
