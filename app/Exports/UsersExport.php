<?php

namespace App\Exports;

use App\Http\Resources\UserCollection;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    use Exportable;

    public function headings(): array
    {
        return [
            '#',
            'Username',
            'Primary Branch',
            'Created At',
            'Status',
            'Last Login At',
            'Last Logout At',
            'Secondary Branches',
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
        $users = User::currentBranch()->get();

        return UserCollection::collection($users);
    }
}
