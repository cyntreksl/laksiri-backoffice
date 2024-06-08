<?php

namespace App\Actions\Container\Loading;

use App\Actions\Container\UpdateContainerStatus;
use App\Actions\HBL\HBLPackage\MarkAsLoaded;
use App\Enum\ContainerStatus;
use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateDraftLoadedContainer
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(array $data)
    {
        try {
            $container = Container::find($data['container_id']);

            foreach ($data['packages'] as $package) {

                $container->hbl_packages()->attach($package['id'], [
                    'status' => 'draft',
                    'loaded_by' => auth()->id(),
                ]);

                MarkAsLoaded::run($package['id']);
            }

            UpdateContainerStatus::run($container, ContainerStatus::DRAFT->value);

        } catch (\Exception $e) {
            throw new \Exception('Failed to create draft loaded container: '.$e->getMessage());
        }
    }
}
