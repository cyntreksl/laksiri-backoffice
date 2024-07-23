<?php

namespace App\Exports;

use App\Factory\Container\FilterFactory;
use App\Models\Container;
use App\Models\Scopes\BranchScope;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoadedShipmentsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithStyles
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
            'Branch',
            'Cargo Type',
            'Container Type',
            'Reference',
            'BL Number',
            'AWB Number',
            'Container Number',
            'Seal Number',
            'Maximum Volume',
            'Minimum Volume',
            'Maximum Weight',
            'Minimum Weight',
            'Maximum Volumetric Weight',
            'Minimum Volumetric Weight',
            'Estimated Time of Departure',
            'Estimated Time of Arrival',
            'Vessel Name',
            'Voyage Number',
            'Shipping Line',
            'Part of Loading',
            'Part of Discharge',
            'Flight Number',
            'Airline Name',
            'Airport of Departure',
            'Airport of Arrival',
            'Cargo Class',
            'Status',
            'System Status',
            'Loading Started At',
            'Loading Ended At',
            'Unloading Started At',
            'Unloading Ended At',
            'Loading Started By',
            'Loading Ended By',
            'Unloading Started By',
            'Unloading Ended By',
            'Note',
            'Is Reached',
            'Reached Date',
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
        if (request()->header('referer') === route('arrival.shipments-arrivals.index')) {
            $query = Container::query()->loadedContainers()->withoutGlobalScope(BranchScope::class);
        } else {
            $query = Container::query()->loadedContainers();
        }

        FilterFactory::apply($query, $this->filters);

        return $query;
    }
}
