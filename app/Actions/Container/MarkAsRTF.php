<?php

namespace App\Actions\Container;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsRTF
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(Container $container): void
    {
        try {
            $container->rtfRecords()->create([
                'is_rtf' => true,
                'rtf_by' => auth()->id(),
                'note' => 'RTF marked due to completion of checks.',
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as rtf container: '.$e->getMessage());
        }
    }
}
