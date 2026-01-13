<?php

namespace App\Actions\HBL\HBLPackage;

use App\Models\HBLPackage;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsDetain
{
    use AsAction;

    public function handle(
        HBLPackage $HBLPackage,
        string $detainType = 'RTF',
        ?string $detainReason = null,
        ?string $remarks = null
    ): void {
        try {
            $HBLPackage->detainRecords()->create([
                'is_rtf' => true,
                'detain_type' => $detainType,
                'action' => 'detain',
                'detain_reason' => $detainReason ?? "Package detained by {$detainType}",
                'remarks' => $remarks,
                'rtf_by' => auth()->id(),
                'note' => "Package detained by {$detainType} due to completion of checks.",
                'entity_level' => 'package',
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to detain HBL Package: '.$e->getMessage());
        }
    }
}
