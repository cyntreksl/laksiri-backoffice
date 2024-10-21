<?php

namespace App\Http\Controllers;

use App\Interfaces\SettingRepositoryInterface;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingRepositoryInterface $settingRepository,
    ) {
    }

    public function updateInvoiceSettings(Request $request)
    {
        $this->settingRepository->updateSetting($request->all());
    }
}
