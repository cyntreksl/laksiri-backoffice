<?php

namespace App\Actions\HBL\HBLPackage;

use App\Models\HBLPackage;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsDetain
{
    use AsAction;

    public function handle(HBLPackage $HBLPackage, string $detainType = 'RTF'): void
    {
        try {
            $HBLPackage->detainRecords()->create([
                'is_rtf' => true,
                'detain_type' => $detainType,
                'rtf_by' => auth()->id(),
                'note' => "Package detained by {$detainType} due to completion of checks.",
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to detain HBL Package: '.$e->getMessage());
        }
    }
}
