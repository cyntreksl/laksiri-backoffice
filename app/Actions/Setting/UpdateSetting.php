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
        if (array_key_exists('logo', $data) && $data['logo'] === null) {
            unset($data['logo']);
        }
        if (array_key_exists('seal', $data) && $data['seal'] === null) {
            unset($data['seal']);
        }
        $data['branch_id'] = GetUserCurrentBranchID::run();
        $data['notification'] = json_encode($data['notification']);

        return Setting::updateOrCreate(['branch_id' => $data['branch_id']], $data);
    }
}
