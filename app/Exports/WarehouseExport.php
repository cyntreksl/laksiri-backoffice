<?php

namespace App\Exports;

use App\Factory\Warehouse\FilterFactory;
use App\Models\HBL;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WarehouseExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function headings(): array
    {
        return [
            '#',
            'HBL Number',
            'Shipper Name',
            'Cargo Type',
            'HBL Type',
            'Weight',
            'Volume',
            'No of Packages',
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
        $query = HBL::query();

        $query->warehouse();

        $query->with(['packages'])
            ->withSum('packages', 'actual_weight')
            ->withSum('packages', 'volume');

        FilterFactory::apply($query, $this->filters);

        return $query;
    }

    public function map($row): array
    {
        $array = [
            'Id' => $row->id,
            'HBL Number' => $row->hbl_number,
            'Shipper Name' => $row->hbl_name,
            'Cargo Type' => $row->cargo_type,
            'HBL Type' => $row->hbl_type,
            'Weight' => $row['packages_sum_actual_weight'],
            'Volume' => $row['packages_sum_volume'],
            'No of Packages' => count($row->packages),
        ];

        return $array;
    }
}
