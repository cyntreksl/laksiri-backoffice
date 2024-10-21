<?php

namespace App\Repositories;

use App\Actions\Setting\GetSettings;
use App\Actions\Setting\UpdateSetting;
use App\Interfaces\SettingRepositoryInterface;

class SettingRepository implements SettingRepositoryInterface
{
    public function updateSetting(array $data)
    {
        UpdateSetting::run($data);
    }

    public function getSettings()
    {
        return GetSettings::run();
    }
}
