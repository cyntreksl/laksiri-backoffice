<?php

namespace App\Exports;

use App\Factory\CashSettlement\FilterFactory;
use App\Models\HBL;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashSettlementsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
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
            'Name',
            'Pickup Date',
            'Weight',
            'Volume',
            'No of Packages',
            'Amount',
            'Paid Amount',
            'Cargo Mode',
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

        $query->cashSettlement()->whereHas('packages');
        $query->with(['pickup', 'packages'])
            ->withSum('packages', 'weight')
            ->withSum('packages', 'volume');

        FilterFactory::apply($query, $this->filters);

        return $query;
    }

    public function map($row): array
    {
        $array = [
            'Id' => $row->id,
            'HBL Number' => $row->hbl_number,
            'Name' => $row->hbl_name,
            'Pickup Date' => $row->pickup ? $row->pickup['pickup_date'] : $row->created_at->format('Y-m-d'),
            'Weight' => $row['packages_sum_weight'],
            'Volume' => $row['packages_sum_volume'],
            'No of Packages' => count($row->packages),
            'Amount' => $row->grand_total,
            'Paid Amount' => $row->paid_amount,
            'Cargo Mode' => $row->cargo_type,
        ];

        return $array;
    }
}
