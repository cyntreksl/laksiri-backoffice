<?php

namespace App\Actions\HBL\HBLPackage;

use App\Models\HBLPackage;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsUnRTF
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(HBLPackage $HBLPackage): void
    {
        try {
            $HBLPackage->rtfRecords()->create([
                'is_rtf' => false,
                'rtf_by' => auth()->id(),
                'note' => 'Undo RTF.',
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as undo rtf HBL Package: '.$e->getMessage());
        }
    }
}
