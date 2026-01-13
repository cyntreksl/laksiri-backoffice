<?php

namespace App\Actions\HBL\HBLPackage;

use App\Models\HBLPackage;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsUnDetain
{
    use AsAction;

    public function handle(
        HBLPackage $HBLPackage,
        string $liftReason,
        ?string $remarks = null
    ): void {
        try {
            // Get the latest active detain record to reference
            $latestDetain = $HBLPackage->detainRecords()
                ->where('is_rtf', true)
                ->where('action', 'detain')
                ->latest()
                ->first();

            $HBLPackage->detainRecords()->create([
                'is_rtf' => false,
                'detain_type' => $latestDetain?->detain_type,
                'action' => 'lift',
                'lift_reason' => $liftReason,
                'remarks' => $remarks,
                'rtf_by' => $latestDetain?->rtf_by,
                'lifted_by' => auth()->id(),
                'lifted_at' => now(),
                'note' => 'Detain lifted for package.',
                'entity_level' => 'package',
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to lift detain for HBL Package: '.$e->getMessage());
        }
    }
}
