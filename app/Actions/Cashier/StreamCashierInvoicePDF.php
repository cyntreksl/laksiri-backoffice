<?php

namespace App\Actions\Cashier;

use App\Actions\Container\GetContainerWithoutGlobalScopesById;
use App\Actions\HBL\GetHBLByIdWithPackages;
use App\Actions\HBL\HBLCharges\CalculateTax;
use App\Actions\SLInvoice\CreateSLInvoice;
use App\Actions\Tax\GetTaxesByWarehouse;
use App\Actions\User\GetUserById;
use Lorisleiva\Actions\Concerns\AsAction;
use NumberFormatter;
use Symfony\Component\HttpFoundation\Response;
use Wnx\SidecarBrowsershot\BrowsershotLambda;

class StreamCashierInvoicePDF
{
    use AsAction;

    public function handle($hbl): Response
    {
        $hbl = GetHBLByIdWithPackages::run($hbl);
        $hbl->load('destinationCharge');
        $sl_Invoice = $hbl->slInvoices;

        if (! $sl_Invoice) {
            $sl_Invoice = CreateSLInvoice::run($hbl);
        }

        $container = $sl_Invoice && ! is_null($sl_Invoice['container_id'])
            ? GetContainerWithoutGlobalScopesById::run($sl_Invoice['container_id'])
            : $hbl->packages[0]->containers()->withoutGlobalScopes()->first();

        // Use stored demurrage charge from HBLDestinationCharge if available, otherwise use SLInvoice
        $demurrageCharge = $hbl->destinationCharge?->destination_demurrage_charge ?? $sl_Invoice['dmg_charge_amount'] ?? 0;
        $doCharge = $hbl->destinationCharge?->destination_do_charge ?? $sl_Invoice['do_charge'] ?? 0;

        // Apply conditional logic based on payment status
        // 1. If Destination I Charges is paid, set handling, storage, and port charges to 0
        $portChargeAmount = $hbl->is_destination_charges_paid ? 0 : ($sl_Invoice['port_charge_amount'] ?? 0);
        $handlingChargeAmount = $hbl->is_destination_charges_paid ? 0 : ($sl_Invoice['handling_charge_amount'] ?? 0);
        $storageChargeAmount = $hbl->is_destination_charges_paid ? 0 : ($sl_Invoice['storage_charge_amount'] ?? 0);

        // 2. If Demurrage Charge is 0, ensure it shows as 0 in invoice
        $demurrageCharge = ($demurrageCharge == 0) ? 0 : $demurrageCharge;

        // Calculate tax on Destination II charges (demurrage + DO)
        $destination2Total = $demurrageCharge + $doCharge;
        $taxCalculation = CalculateTax::run($destination2Total);
        $taxAmount = $taxCalculation['total_tax'];
        $destination2TotalWithTax = $taxCalculation['amount_with_tax'];

        // Recalculate total with correct demurrage charge
        $totalAmount = $portChargeAmount + $handlingChargeAmount + $storageChargeAmount + $destination2TotalWithTax;
        $stampCharge = $destination2TotalWithTax > 25000 ? 25.00 : 0.00;

        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $total_in_word = strtoupper($formatter->format($totalAmount + $stampCharge));

        $data = [
            'clearing_time' => $sl_Invoice['clearing_time'],
            'date' => $sl_Invoice['date'],
            'vessel' => $container,
            'hbl' => $hbl,
            'grand_volume' => $sl_Invoice['grand_volume'],
            'charges' => [
                'port_charge' => [
                    'rate' => $sl_Invoice['port_charge_rate'],
                    'amount' => $portChargeAmount,
                ],
                'handling_charge' => [
                    'rate' => $sl_Invoice['handling_charge_rate'],
                    'amount' => $handlingChargeAmount,
                ],
                'storage_charge' => [
                    'rate' => $sl_Invoice['storage_charge_rate'],
                    'amount' => $storageChargeAmount,
                ],
                'dmg_charge' => [
                    'rate' => $sl_Invoice['dmg_charge_rate'] ?? 0,
                    'amount' => $demurrageCharge,
                ],
                'do_charge' => $doCharge,
                'tax' => $taxAmount,
                'total' => $totalAmount,
                'stamp_charge' => $stampCharge,
            ],
            'total_in_word' => $total_in_word,
            'by' => GetUserById::run($sl_Invoice['created_by'])->name,
            'taxes' => GetTaxesByWarehouse::run($hbl->warehouse_id)
                ->map(function ($tax) {
                    return [
                        'name' => $tax->name,
                        'rate' => $tax->rate,
                    ];
                })
                ->values()
                ->all(),
        ];

        $template = view('pdf.cashier.invoice', [
            'logoPath' => asset('images/app-logo.png') ?? null,
            'data' => $data,
            'hbl' => $hbl,
        ])->render();

        $filename = 'RECEIPT_'.$hbl['reference'].'.pdf';

        $pdfContent = BrowsershotLambda::html($template)
            ->showBackground()
            ->format('A4')
            ->pdf(); // Generates raw PDF content as a string

        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]
        );
    }
}
