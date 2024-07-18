<?php

namespace App\Actions\Container;

use App\Models\Container;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
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

            foreach ($container->hbl_packages as $package) {
                $hbl = HBL::withoutGlobalScope(BranchScope::class)->find($package->hbl_id);
                $hbl->addStatus('Container Arrival', $container->estimated_time_of_arrival);
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as reached container: '.$e->getMessage());
        }
    }
}
