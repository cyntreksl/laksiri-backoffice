<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsUnDetain
{
    use AsAction;

    public function handle(
        HBL $hbl,
        string $liftReason,
        ?string $remarks = null
    ): void {
        try {
            // Get the latest active detain record to reference
            $latestDetain = $hbl->detainRecords()
                ->where('is_rtf', true)
                ->where('action', 'detain')
                ->latest()
                ->first();

            $hbl->detainRecords()->create([
                'is_rtf' => false,
                'detain_type' => $latestDetain?->detain_type,
                'action' => 'lift',
                'lift_reason' => $liftReason,
                'remarks' => $remarks,
                'rtf_by' => $latestDetain?->rtf_by,
                'lifted_by' => auth()->id(),
                'lifted_at' => now(),
                'note' => 'Detain lifted for HBL.',
                'entity_level' => 'hbl',
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to lift detain for HBL: '.$e->getMessage());
        }
    }
}
