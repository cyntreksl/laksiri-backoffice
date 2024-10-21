<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Interfaces\SettingRepositoryInterface;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingRepositoryInterface $settingRepository,
    ) {
    }

    public function updateInvoiceSettings(UpdateSettingRequest $request)
    {
        $this->settingRepository->updateSetting($request->all());
    }
}
