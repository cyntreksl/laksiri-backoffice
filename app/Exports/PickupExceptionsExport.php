<?php

namespace App\Exports;

use App\Factory\Pickup\FilterFactory;
use App\Models\PickupException;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PickupExceptionsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithStyles
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
            'PickUp ID',
            'Driver ID',
            'Zone ID',
            'Branch ID',
            'Reference',
            'Name',
            'Picker Note',
            'Address',
            'Pickup Date',
            'Auth',
            'Driver Assigned At',
            'System Status',
            'Created By',
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
        $query = PickupException::query();

        FilterFactory::apply($query, $this->filters);

        return $query;
    }
}
