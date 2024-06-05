<?php

namespace App\Actions\Container;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateContainerStatus
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(string|int $container_id, string $status)
    {
        try {
            Container::find($container_id)->update([
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update status of container: '.$e->getMessage());
        }
    }
}
