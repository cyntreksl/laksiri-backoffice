<?php

namespace App\Actions\HBL;

use App\Actions\HBL\GenerateHBLReferenceNumber;
use App\Actions\User\GetUserCurrentBranch;
use App\Actions\User\GetUserCurrentBranchID;
use App\Models\HBL;
use App\Models\HBLStatusChange;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class HBLSystemStatusLog
{
    use AsAction;

    public function handle(HBL $HBL, float $status): HBLStatusChange
    {
        $HBLStatus = new HBLStatusChange();
        $HBLStatus->hbl_id = $HBL->id;
        $HBLStatus->title = $this->title($status);
        $HBLStatus->message = sprintf($this->message($status),$HBL->hbl);
        $HBLStatus->created_by = Auth::id();
        $HBLStatus->save();

        return $HBLStatus;
    }

    private function title(float $status): string
    {
        $title = "HBL created";
        switch ($status){
            case 3.0:
                $title = "HBL created";
                break;
            case 3.1:
                $title = "HBL created - Job Converted to HBL";
                break;
            case 4.0:
                $title = "Cash Collected";
                break;
        }

        return $title;
    }

    private function message(float $status): string
    {
        $title = "HBL #%s created";
        switch ($status){
            case 3.0:
                $title = "HBL #%s created";
                break;
            case 3.1:
                $title = "HBL #%s created - Job Converted to HBL";
                break;
            case 4.0:
                $title = "HBL #%s cash collected";
                break;
        }

        return $title;
    }
}
