<?php

namespace App\Actions\Container;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsUnRTF
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(Container $container): void
    {
        try {
            $container->rtfRecords()->create([
                'is_rtf' => false,
                'rtf_by' => auth()->id(),
                'note' => 'Undo RTF.',
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as unrtf container: '.$e->getMessage());
        }
    }
}
