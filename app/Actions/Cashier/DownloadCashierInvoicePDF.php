<?php

namespace App\Actions\Cashier;

use App\Actions\Container\GetContainerWithoutGlobalScopesById;
use App\Actions\HBL\GetHBLByIdWithPackages;
use App\Actions\SLInvoice\CreateSLInvoice;
use App\Actions\User\GetUserById;
use Lorisleiva\Actions\Concerns\AsAction;
use NumberFormatter;
use Wnx\SidecarBrowsershot\BrowsershotLambda;

class DownloadCashierInvoicePDF
{
    use AsAction;

    public function handle($hbl)
    {
        $hbl = GetHBLByIdWithPackages::run($hbl);
        $sl_Invoice = $hbl->slInvoices;
        if (! $sl_Invoice) {
            $sl_Invoice = CreateSLInvoice::run($hbl);
        }
        $container = $sl_Invoice && ! is_null($sl_Invoice['container_id'])
            ? GetContainerWithoutGlobalScopesById::run($sl_Invoice['container_id'])
            : $hbl->packages[0]->containers()->withoutGlobalScopes()->first();

        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $total_in_word = strtoupper($formatter->format($sl_Invoice['total']));

        $data = [
            'clearing_time' => $sl_Invoice['clearing_time'],
            'date' => $sl_Invoice['date'],
            'vessel' => $container,
            'hbl' => $hbl,
            'grand_volume' => $sl_Invoice['grand_volume'],
            'charges' => [
                'port_charge' => [
                    'rate' => $sl_Invoice['port_charge_rate'],
                    'amount' => $sl_Invoice['port_charge_amount'],
                ],
                'handling_charge' => [
                    'rate' => $sl_Invoice['handling_charge_rate'],
                    'amount' => $sl_Invoice['handling_charge_amount'],
                ],
                'storage_charge' => [
                    'rate' => $sl_Invoice['storage_charge_rate'],
                    'amount' => $sl_Invoice['storage_charge_amount'],
                ],
                'dmg_charge' => [
                    'rate' => $sl_Invoice['dmg_charge_rate'],
                    'amount' => $sl_Invoice['dmg_charge_amount'],
                ],
                'total' => $sl_Invoice['total'],
                'do_charge' => $sl_Invoice['do_charge'],
                'stamp_charge' => $sl_Invoice['stamp_charge'],
            ],
            'total_in_word' => $total_in_word,
            'by' => GetUserById::run($sl_Invoice['created_by'])->name,
        ];

        $template = view('pdf.cashier.invoice', [
            'logoPath' => asset('images/app-logo.png') ?? null,
            'data' => $data,
            'hbl' => $hbl,
        ])->render();

        $filename = 'RECEIPT'.$hbl['reference'].'.pdf';

        $filePath = storage_path("app/public/{$filename}");

        BrowsershotLambda::html($template)
            ->showBackground()
            ->format('A4')
            ->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
