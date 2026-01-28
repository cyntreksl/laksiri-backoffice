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

class DownloadCashierInvoicePDF
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
        $container = $sl_Invoice && ! is_null($sl_Invoice->container_id)
            ? GetContainerWithoutGlobalScopesById::run($sl_Invoice->container_id)
            : $hbl->packages[0]->containers()->withoutGlobalScopes()->first();
        
        // Prioritize HBLDestinationCharge data if available, otherwise use SLInvoice
        $hasDestinationCharge = $hbl->destinationCharge !== null;
        
        // Apply conditional logic based on payment status
        // 1. If Destination I Charges is paid, set handling, storage, and port charges to 0
        $handlingChargeAmount = $hbl->is_destination_charges_paid ? 0 : ($hasDestinationCharge 
            ? ($hbl->destinationCharge->destination_handling_charge ?? 0)
            : ($sl_Invoice->handling_charge_amount ?? 0));
            
        $portChargeAmount = $hbl->is_destination_charges_paid ? 0 : ($hasDestinationCharge 
            ? ($hbl->destinationCharge->destination_slpa_charge ?? 0)
            : ($sl_Invoice->port_charge_amount ?? 0));
            
        $storageChargeAmount = $hbl->is_destination_charges_paid ? 0 : ($hasDestinationCharge 
            ? ($hbl->destinationCharge->destination_bond_charge ?? 0)
            : ($sl_Invoice->storage_charge_amount ?? 0));
        
        // Get Destination II charges (demurrage, DO)
        $demurrageCharge = $hasDestinationCharge
            ? ($hbl->destinationCharge->destination_demurrage_charge ?? 0)
            : ($sl_Invoice->dmg_charge_amount ?? 0);
            
        $doCharge = $hasDestinationCharge
            ? ($hbl->destinationCharge->destination_do_charge ?? 0)
            : ($sl_Invoice->do_charge ?? 0);
        
        // 2. If Demurrage Charge is 0, ensure it shows as 0 in invoice
        $demurrageCharge = ($demurrageCharge == 0) ? 0 : $demurrageCharge;
        
        // Calculate tax on Destination II charges (demurrage + DO)
        $destination2Total = $demurrageCharge + $doCharge;
        $taxCalculation = CalculateTax::run($destination2Total);
        $taxAmount = $taxCalculation['total_tax'];
        $destination2TotalWithTax = $taxCalculation['amount_with_tax'];
        
        // Calculate total
        $totalAmount = $portChargeAmount + $handlingChargeAmount + $storageChargeAmount + $destination2TotalWithTax;
        $stampCharge = $destination2TotalWithTax > 25000 ? 25.00 : 0.00;

        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $total_in_word = strtoupper($formatter->format($totalAmount + $stampCharge));

        $collectedPackages = collect($hbl->packages)->filter(function ($package) {
            return ! empty($package['unload_date']) ||
                ! empty($package['arrived_at']) ||
                ! empty($package['unloaded_at']);
        })->count();
        
        // Calculate unit rates for display
        $grandVolume = $sl_Invoice->grand_volume ?? 0;
        $packageCount = $hbl->packages->count();
        
        $portChargeRate = $grandVolume > 0 ? ($portChargeAmount / $grandVolume) : 0;
        $handlingChargeRate = $packageCount > 0 ? ($handlingChargeAmount / $packageCount) : 0;
        $storageChargeRate = $grandVolume > 0 ? ($storageChargeAmount / $grandVolume) : 0;
        $dmgChargeRate = $grandVolume > 0 ? ($demurrageCharge / ($grandVolume * 35)) : 0;

        $data = [
            'clearing_time' => $sl_Invoice->clearing_time,
            'date' => $sl_Invoice->date,
            'vessel' => $container,
            'hbl' => $hbl,
            'grand_volume' => $grandVolume,
            'charges' => [
                'port_charge' => [
                    'rate' => $portChargeRate,
                    'amount' => $portChargeAmount,
                ],
                'handling_charge' => [
                    'rate' => $handlingChargeRate,
                    'amount' => $handlingChargeAmount,
                ],
                'storage_charge' => [
                    'rate' => $storageChargeRate,
                    'amount' => $storageChargeAmount,
                ],
                'dmg_charge' => [
                    'rate' => $dmgChargeRate,
                    'amount' => $demurrageCharge,
                ],
                'do_charge' => $doCharge,
                'tax' => $taxAmount,
                'total' => $totalAmount,
                'stamp_charge' => $stampCharge,
            ],
            'bond_storage_numbers' => $hbl->packages->pluck('bond_storage_number')->filter()->values()->all(),
            'total_in_word' => $total_in_word,
            'by' => GetUserById::run($sl_Invoice->created_by)->name,
            'taxes' => GetTaxesByWarehouse::run($hbl->warehouse_id)
                ->map(function ($tax) {
                    return [
                        'name' => $tax->name,
                        'rate' => $tax->rate,
                    ];
                })
                ->values()
                ->all(),
            'collected_packages' => $collectedPackages,
            'remaining_packages' => count($hbl->packages) - $collectedPackages,
        ];

        $template = view('pdf.cashier.invoice-v2', [
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
