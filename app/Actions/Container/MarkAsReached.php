<?php

namespace App\Actions\Container;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsReached
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(Container $container): void
    {
        try {
            $container->update([
                'is_reached' => true,
                'reached_date' => now(),
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as reached container: '.$e->getMessage());
        }
    }
}
