<?php

namespace App\Exports;

use App\Factory\CashSettlement\FilterFactory;
use App\Models\HBL;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashSettlementsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithStyles
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
            'Reference',
            'Warehouse Zone Id',
            'Branch',
            'Pickup Id',
            'Cargo Type',
            'HBL Type',
            'HBL',
            'HBL Name',
            'Email',
            'Contact Number',
            'NIC',
            'IQ Number',
            'Address',
            'Consignee Name',
            'Consignee NIC',
            'Consignee Contact',
            'Consignee Address',
            'Consignee Note',
            'Warehouse',
            'Freight Charge',
            'Bill Charge',
            'Other Charge',
            'Discount',
            'Paid Amount',
            'Grand Total',
            'Status',
            'System Status',
            'Created By',
            'Is Hold',
            'Is Short Loading',
            'Created At',
            'Updated At',
            'Deleted At',
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

        $query->cashSettlement();

        FilterFactory::apply($query, $this->filters);

        return $query;
    }
}
