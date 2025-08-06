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
            'Address',
            'Pickup Date',
            'Weight',
            'Volume',
            'No of Packages',
            'Destination Charges',
            'Agent Charges',
            'Amount',
            'Paid Amount',
            'Cargo Mode',
            'Driver',
            'Source',
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
            'Name' => $row->hbl_name,
            'Address' => $row->address,
            'Pickup Date' => $row->created_at->format('Y-m-d'),
            'Weight' => $row['packages_sum_actual_weight'],
            'Volume' => $row['packages_sum_volume'],
            'No of Packages' => count($row->packages),
            'Destination Charges' => number_format($row->destinationCharge->destination_1_total_with_tax / $row->destinationCharge->base_currency_rate_in_lkr ?? 0, 2),
            'Agent Charges' => number_format($row->departureCharge->departure_grand_total ?? 0, 2),
            'Amount' => $row->grand_total,
            'Paid Amount' => $row->paid_amount,
            'Cargo Mode' => $row->cargo_type,
            'Driver' => $row->pickup?->driver?->name,
            'Source' => $row->user?->name,
        ];

        return $array;
    }
}
