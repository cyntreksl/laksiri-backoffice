<?php

namespace App\Actions\Setting;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Setting;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateSetting
{
    use AsAction;

    public function handle(array $data)
    {
        $data['branch_id'] = GetUserCurrentBranchID::run();

        Setting::updateOrCreate(['branch_id' => $data['branch_id']], $data);
    }
}
