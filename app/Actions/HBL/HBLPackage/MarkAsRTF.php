<?php

namespace App\Actions\HBL\HBLPackage;

use App\Models\HBLPackage;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsRTF
{
    use AsAction;

    public function handle(HBLPackage $HBLPackage): void
    {
        try {
            $HBLPackage->rtfRecords()->create([
                'is_rtf' => true,
                'rtf_by' => auth()->id(),
                'note' => 'RTF marked due to completion of checks.',
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as rtf HBL Package: '.$e->getMessage());
        }
    }
}
