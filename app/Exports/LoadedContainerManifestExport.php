<?php

namespace App\Exports;

use App\Models\Container;
use App\Models\HBL;
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
        return Container::query()->where('id', $this->container->id);
    }

    /**
     * @param  Container  $container
     */
    public function map($container): array
    {
        $data = [];

        foreach ($container->hbl_packages->groupBy('hbl_id') as $hblId => $loadedHBLPackages) {
            $hbl = HBL::find($hblId);

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

            $data[] = [
                '',
                $hbl->address,
                $hbl->consignee_address,
                '',
                '',
                '',
                '',
            ];

            $data[] = [
                '',
                $hbl->nic,
                $hbl->consignee_nic,
                '',
                '',
                '',
                '',
            ];

            $data[] = [
                '',
                $hbl->contact_number,
                $hbl->consignee_contact,
                '',
                '',
                '',
                '',
            ];
        }

        return $data;
    }
}
