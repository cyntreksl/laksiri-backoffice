<?php

namespace App\Actions\Shipment;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsDetain
{
    use AsAction;

    public function handle(
        Container $container,
        string $detainType = 'RTF',
        ?string $detainReason = null,
        ?string $remarks = null
    ): void {
        try {
            $container->detainRecords()->create([
                'is_rtf' => true,
                'detain_type' => $detainType,
                'action' => 'detain',
                'detain_reason' => $detainReason ?? "Shipment detained by {$detainType}",
                'remarks' => $remarks,
                'rtf_by' => auth()->id(),
                'note' => "Shipment detained by {$detainType}.",
                'entity_level' => 'shipment',
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to detain Shipment: '.$e->getMessage());
        }
    }
}
