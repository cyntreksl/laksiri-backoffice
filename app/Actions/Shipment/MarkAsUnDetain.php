<?php

namespace App\Actions\Shipment;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsUnDetain
{
    use AsAction;

    public function handle(
        Container $container,
        string $liftReason,
        ?string $remarks = null
    ): void {
        try {
            // Get the latest active detain record to reference
            $latestDetain = $container->detainRecords()
                ->where('is_rtf', true)
                ->where('action', 'detain')
                ->latest()
                ->first();

            $container->detainRecords()->create([
                'is_rtf' => false,
                'detain_type' => $latestDetain?->detain_type,
                'action' => 'lift',
                'lift_reason' => $liftReason,
                'remarks' => $remarks,
                'rtf_by' => $latestDetain?->rtf_by,
                'lifted_by' => auth()->id(),
                'lifted_at' => now(),
                'note' => 'Detain lifted for Shipment.',
                'entity_level' => 'shipment',
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to lift detain for Shipment: '.$e->getMessage());
        }
    }
}
