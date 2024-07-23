<?php

namespace App\Exports;

use App\Factory\Pickup\FilterFactory;
use App\Models\PickUp;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PickupsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithStyles
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
            'Branch',
            'Cargo Type',
            'Name',
            'Email',
            'Contact Number',
            'Address',
            'Location',
            'Longitude',
            'Latitude',
            'Zone',
            'Notes',
            'Driver',
            'Driver Assigned At',
            'HBL',
            'Pickup Date',
            'Pickup Time Start',
            'Pickup Time End',
            'Is Urgent',
            'Is Important',
            'Pickup Order',
            'Status',
            'Created By',
            'Created At',
            'Updated At',
            'Deleted At',
            'System Status',
            'Pickup Type',
            'Pickup Note',
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
        $query = PickUp::query()
            ->whereIn('system_status', [PickUp::SYSTEM_STATUS_PICKUP_CREATED, PickUp::SYSTEM_STATUS_DRIVER_ASSIGNED]);

        FilterFactory::apply($query, $this->filters);

        return $query;
    }
}
