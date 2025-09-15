<?php

namespace App\Actions\HBL\HBLPackage;

use App\Models\HBLPackage;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsUnDetain
{
    use AsAction;

    public function handle(HBLPackage $HBLPackage): void
    {
        try {
            $HBLPackage->detainRecords()->create([
                'is_rtf' => false,
                'detain_type' => null,
                'rtf_by' => auth()->id(),
                'note' => 'Detain lifted for package.',
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to lift detain for HBL Package: '.$e->getMessage());
        }
    }
}
