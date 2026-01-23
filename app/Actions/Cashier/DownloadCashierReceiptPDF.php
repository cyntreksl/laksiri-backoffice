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
use Wnx\SidecarBrowsershot\BrowsershotLambda;

class DownloadCashierReceiptPDF
{
    use AsAction;

    public function handle($hbl)
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
        
        // Get the latest cashier payment to show what was actually paid
        $latestPayment = \App\Models\CashierHBLPayment::where('hbl_id', $hbl->id)
            ->latest('created_at')
            ->first();

        // Use stored demurrage charge from HBLDestinationCharge if available, otherwise use SLInvoice
        $demurrageCharge = $hbl->destinationCharge?->destination_demurrage_charge ?? $sl_Invoice['dmg_charge_amount'] ?? 0;
        $doCharge = $hbl->destinationCharge?->destination_do_charge ?? $sl_Invoice['do_charge'] ?? 0;
        $otherCharge = $hbl->destinationCharge?->destination_other_charge ?? 0;
        
        // Calculate tax on Destination II charges (demurrage + DO)
        $destination2Total = $demurrageCharge + $doCharge;
        $taxCalculation = CalculateTax::run($destination2Total);
        $taxAmount = $taxCalculation['total_tax'];
        $destination2TotalWithTax = $taxCalculation['amount_with_tax'];
        
        // Recalculate total with correct demurrage charge
        $portChargeAmount = $sl_Invoice['port_charge_amount'] ?? 0;
        $handlingChargeAmount = $sl_Invoice['handling_charge_amount'] ?? 0;
        $storageChargeAmount = $sl_Invoice['storage_charge_amount'] ?? 0;
        
        // Calculate agent charges (departure + destination I if applicable)
        $agentCharges = $hbl->grand_total ?? 0;
        $agentPaid = $hbl->paid_amount ?? 0;
        $agentOutstanding = max(0, $agentCharges - $agentPaid);
        
        // Total outstanding = Agent outstanding + SL Portal charges
        $totalAmount = $portChargeAmount + $handlingChargeAmount + $storageChargeAmount + $destination2TotalWithTax + $otherCharge;
        $stampCharge = $destination2TotalWithTax > 25000 ? 25.00 : 0.00;
        
        // Calculate outstanding amount (what needs to be paid)
        $outstandingAmount = $agentOutstanding + $totalAmount + $stampCharge;
        
        // If there's a payment record, use the paid amount from it
        $paidAmount = $latestPayment ? $latestPayment->paid_amount : $outstandingAmount;

        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $total_in_word = strtoupper($formatter->format($paidAmount));

        $data = [
            'clearing_time' => $sl_Invoice['clearing_time'],
            'date' => $sl_Invoice['date'],
            'vessel' => $container,
            'hbl' => $hbl,
            'grand_volume' => $sl_Invoice['grand_volume'],
            'charges' => [
                'agent_charges' => $agentCharges,
                'agent_paid' => $agentPaid,
                'agent_outstanding' => $agentOutstanding,
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
                'other_charge' => $otherCharge,
                'tax' => $taxAmount,
                'total' => $totalAmount,
                'stamp_charge' => $stampCharge,
                'outstanding' => $outstandingAmount,
                'paid_amount' => $paidAmount,
            ],
            'total_in_word' => $total_in_word,
            'by' => $latestPayment ? $latestPayment->verifiedBy->name : (GetUserById::run($sl_Invoice['created_by'])->name ?? 'N/A'),
            'payment' => $latestPayment,
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

        $template = view('pdf.cashier.receipt', [
            'logoPath' => asset('images/app-logo.png') ?? null,
            'data' => $data,
            'hbl' => $hbl,
        ])->render();

        $filename = 'POS_RECEIPT_'.$hbl['reference'].'.pdf';

        $filePath = storage_path("app/public/{$filename}");

        BrowsershotLambda::html($template)
            ->showBackground()
//            ->paperSize(80, 297, 'mm') // Save usually defaults to A4, ensuring paper size is set
            ->paperSize(80, 297, 'mm')
            ->margins(0,0,0,0)
            ->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
