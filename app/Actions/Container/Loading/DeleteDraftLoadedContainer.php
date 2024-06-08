<?php

namespace App\Actions\Container\Loading;

use App\Actions\HBL\HBLPackage\MarkAsUnloaded;
use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteDraftLoadedContainer
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(array $data)
    {
        try {
            $container = Container::find($data['container_id']);

            $container->hbl_packages()->detach($data['package_id']);

            MarkAsUnloaded::run($data['package_id']);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete draft loaded container: '.$e->getMessage());
        }
    }
}
