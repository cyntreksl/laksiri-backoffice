<?php

namespace App\Repositories;

use App\Actions\Setting\GetSettings;
use App\Actions\Setting\UpdateSetting;
use App\Interfaces\SettingRepositoryInterface;

class SettingRepository implements SettingRepositoryInterface
{
    public function updateSetting(array $data)
    {
        $settings = UpdateSetting::run($data);

        if (isset($data['logo'])) {
            $settings->updateFile($data['logo'], 'logo', 'settings/invoice/logos');
        }
        // seal image
        if (isset($data['seal'])) {
            $settings->updateFile($data['seal'], 'seal', 'settings/invoice/logos');
        }

    }

    public function getSettings()
    {
        return GetSettings::run();
    }
}
