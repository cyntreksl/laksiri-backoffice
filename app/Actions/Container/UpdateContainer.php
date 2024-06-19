<?php

namespace App\Actions\Container;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateContainer
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(Container $container, array $data)
    {
        try {
            $container->update($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update container: '.$e->getMessage());
        }
    }
}
