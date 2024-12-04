<?php

namespace App\Actions\Cashier;

use App\Actions\HBL\GetHBLById;
use App\Actions\Setting\GetSettings;
use App\Models\HBL;
use Barryvdh\DomPDF\Facade\Pdf;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadGatePassPDF
{
    use AsAction;

    public function handle($hbl)
    {
        $hbl = GetHBLById::run($hbl);

        $data = [
            'clearing_time' => date('H:i:s'),
            'date' => date('Y-m-d'),
            'hbl' => $hbl,
        ];
//        dd($data);
        $pdf = Pdf::loadView('pdf.cashier.gatePass',['data'=>$data, 'hbl'=>$hbl])->setPaper('a4');

        $filename = $hbl['reference'].'.pdf';

        return $pdf->stream($filename);
    }
}
