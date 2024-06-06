<?php

namespace App\Exports;

use App\Models\Container;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoadedContainerManifestExport implements FromQuery, WithHeadings, WithStyles
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
        return Container::query();
    }
}
