<?php

namespace App\Observers;

use App\Enum\ContainerStatus;
use App\Models\Container;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;

class ContainerObserver
{
    /**
     * Handle the Container "created" event.
     */
    /**
     * Handle the Container "updated" event.
     */
    public function updated(Container $container): void
    {
        if ($container->wasChanged('status') && $container->status === ContainerStatus::IN_TRANSIT->value) {
            foreach ($container->hbl_packages as $package) {
                $hbl = HBL::withoutGlobalScope(BranchScope::class)->find($package->hbl_id);
                $hbl->addStatus('Container '.ucwords(strtolower(ContainerStatus::IN_TRANSIT->value)));
            }
        }

        if ($container->wasChanged('status') && $container->status === ContainerStatus::REACHED_DESTINATION->value) {
            foreach ($container->hbl_packages as $package) {
                $hbl = HBL::withoutGlobalScope(BranchScope::class)->find($package->hbl_id);
                $hbl->addStatus('Container '.ucwords(strtolower(ContainerStatus::REACHED_DESTINATION->value)));
            }
        }
    }
}
