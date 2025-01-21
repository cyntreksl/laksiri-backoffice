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
        if ($data['logo'] === null) {
            unset($data['logo']);
        }
        if ($data['seal'] === null) {
            unset($data['seal']);
        };
        $data['branch_id'] = GetUserCurrentBranchID::run();

        return Setting::updateOrCreate(['branch_id' => $data['branch_id']], $data);
    }
}
