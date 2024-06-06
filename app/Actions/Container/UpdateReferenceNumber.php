<?php

namespace App\Actions\Container;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateReferenceNumber
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(Container $container, string $reference)
    {
        try {
            $container->update([
                'reference' => $reference,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update reference of container: '.$e->getMessage());
        }
    }
}
