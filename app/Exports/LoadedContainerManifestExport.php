<?php

namespace App\Exports;

use App\Models\Container;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoadedContainerManifestExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function headings(): array
    {
        return [
            'HBL',
            'Shipper Details',
            'Consignee Details',
            'Type',
            'Quantity',
            'Volume',
            'Weight',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function query()
    {
        return Container::query()
            ->withoutGlobalScope(BranchScope::class)
            ->with(['hbl_packages' => function ($query) {
                $query->withoutGlobalScope(BranchScope::class);
            }])
            ->where('id', $this->container->id);
    }

    public function map($row): array
    {
        $data = [];

        foreach ($row->hbl_packages->groupBy('hbl_id') as $hblId => $loadedHBLPackages) {
            $hbl = HBL::withoutGlobalScope(BranchScope::class)->find($hblId);
            if (! $hbl) {
                continue;
            } // Skip if HBL is missing

            $isFirst = true;
            $totalQuantity = $loadedHBLPackages->sum('quantity');

            foreach ($loadedHBLPackages as $hbl_package) {
                $data[] = [
                    $isFirst ? $hbl->hbl_number ?: $hbl->reference : '',
                    $isFirst ? $hbl->hbl_name : '',
                    $isFirst ? $hbl->consignee_name : '',
                    $hbl_package->package_type,
                    $isFirst ? $totalQuantity : '',
                    $hbl_package->volume,
                    $hbl_package->weight,
                ];

                $isFirst = false;
            }

            // Add additional rows (address, NIC, etc.)
            $data[] = ['', $hbl->address, $hbl->consignee_address, '', '', '', ''];
            $data[] = ['', $hbl->nic, $hbl->consignee_nic, '', '', '', ''];
            $data[] = ['', $hbl->contact_number, $hbl->consignee_contact, '', '', '', ''];
        }

        return $data;
    }
}
