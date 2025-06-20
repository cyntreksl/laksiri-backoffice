<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsRTF
{
    use AsAction;

    public function handle(HBL $hbl): void
    {
        try {
            $hbl->rtfRecords()->create([
                'is_rtf' => true,
                'rtf_by' => auth()->id(),
                'note' => 'RTF marked due to completion of checks.',
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as rtf HBL: '.$e->getMessage());
        }
    }
}
