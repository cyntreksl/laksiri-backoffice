<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsDetain
{
    use AsAction;

    public function handle(
        HBL $hbl,
        string $detainType = 'RTF',
        ?string $detainReason = null,
        ?string $remarks = null
    ): void {
        try {
            $hbl->detainRecords()->create([
                'is_rtf' => true,
                'detain_type' => $detainType,
                'action' => 'detain',
                'detain_reason' => $detainReason ?? "HBL detained by {$detainType}",
                'remarks' => $remarks,
                'rtf_by' => auth()->id(),
                'note' => "HBL detained by {$detainType}.",
                'entity_level' => 'hbl',
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to detain HBL: '.$e->getMessage());
        }
    }
}
