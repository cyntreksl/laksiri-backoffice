<?php

namespace App\Actions\Zone;

use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteZone
{
    use AsAction;

    public function handle(Zone $zone): void
    {
        try {
            DB::beginTransaction();
            $zone->areas()->delete();
            $zone->areas()->detach();
            $zone->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

}
