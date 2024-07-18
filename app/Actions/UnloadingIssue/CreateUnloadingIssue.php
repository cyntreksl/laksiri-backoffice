<?php

namespace App\Actions\UnloadingIssue;

use App\Models\HBL;
use App\Models\HBLPackage;
use App\Models\Scopes\BranchScope;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateUnloadingIssue
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(array $data)
    {
        try {
            if (isset($data['hbl_package_id'])) {
                $hbl_package = HBLPackage::withoutGlobalScope(BranchScope::class)
                    ->find($data['hbl_package_id']);

                $hbl_package->unloadingIssue()->create($data);

                if ($data['rtf']) {
                    $hbl = HBL::withoutGlobalScope(BranchScope::class)->find($hbl_package->hbl_id);

                    $hbl->addStatus('Blocked By RTF');
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to create container: '.$e->getMessage());
        }
    }
}
