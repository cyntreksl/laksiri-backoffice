<?php

namespace App\Exports;

use App\Factory\User\FilterFactory;
use App\Http\Resources\DriverCollection;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DriversExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
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
            'Name',
            'Username',
            'Primary Branch',
            'Created At',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function collection()
    {
        $query = User::query()->role('driver')->currentBranch();

        FilterFactory::apply($query, $this->filters);

        $drivers = $query->get();

        return DriverCollection::collection($drivers);
    }
}
