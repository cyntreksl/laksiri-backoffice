<?php

namespace App\Exports;

use App\Models\Container;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProofOfDeliveryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function collection()
    {
        $hbls = collect();

        // Get all HBLs for this container that are Door to Door
        foreach ($this->container->hbl_packages->groupBy('hbl_id') as $hblId => $packages) {
            $hbl = HBL::withoutGlobalScope(BranchScope::class)
                ->with(['packages', 'mhbl'])
                ->find($hblId);

            if ($hbl && $hbl->hbl_type === 'Door to Door') {
                $hbls->push($hbl);
            }
        }

        return $hbls;
    }

    public function headings(): array
    {
        return [
            'HBL Number',
            'Consignee Contact Numbers',
            'Package Type',
            'No of Packages',
            'Consignee Name',
            'Consignee Address',
            'Weight (KG)',
            'Total Volume (CBM)',
            'Consignee PP or NIC',
        ];
    }

    public function map($hbl): array
    {
        // Calculate totals from packages
        $totalPackages = $hbl->packages->sum('quantity');
        $totalWeight = $hbl->packages->sum('actual_weight') ?: $hbl->packages->sum('weight');
        $totalVolume = $hbl->packages->sum('volume');

        // Get package types (comma-separated if multiple)
        $packageTypes = $hbl->packages->pluck('package_type')->unique()->implode(', ');
        if (empty($packageTypes)) {
            $packageTypes = 'Carton';
        }

        // Combine contact numbers
        $contactNumbers = collect([
            $hbl->consignee_contact,
            $hbl->consignee_additional_mobile_number,
            $hbl->consignee_whatsapp_number,
        ])->filter()->unique()->implode(', ');

        return [
            $hbl->hbl_number ?: $hbl->reference,
            $contactNumbers,
            $packageTypes,
            $totalPackages,
            $hbl->consignee_name,
            $hbl->consignee_address,
            number_format($totalWeight, 2),
            number_format($totalVolume, 3),
            "\t" . $hbl->consignee_nic,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'POD - '.$this->container->reference;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                
                // Format column I (Consignee PP or NIC) as text
                $sheet->getStyle('I2:I' . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);
            },
        ];
    }
}
