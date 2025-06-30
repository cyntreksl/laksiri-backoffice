<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsUnRTF
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(HBL $hbl): void
    {
        try {
            $hbl->rtfRecords()->create([
                'is_rtf' => false,
                'rtf_by' => auth()->id(),
                'note' => 'Undo RTF.',
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as undo rtf HBL: '.$e->getMessage());
        }
    }
}
