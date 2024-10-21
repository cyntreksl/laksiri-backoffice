<?php

namespace App\Interfaces;

interface SettingRepositoryInterface
{
    public function getSettings();

    public function updateSetting(array $data);
}
