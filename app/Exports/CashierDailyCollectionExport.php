<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashierDailyCollectionExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data['payments'];
    }

    public function headings(): array
    {
        return [
            'Date',
            'Receipt Number',
            'Invoice Number',
            'HBL Reference',
            'Customer Name',
            'Customer Contact',
            'Amount (LKR)',
            'Status',
            'Collected By',
            'Notes'
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->created_at->format('Y-m-d H:i:s'),
            $payment->receipt_number ?? 'N/A',
            $payment->invoice_number ?? 'N/A',
            $payment->token->reference ?? 'N/A',
            $payment->token->customer->name ?? 'N/A',
            $payment->token->customer->contact ?? 'N/A',
            number_format($payment->paid_amount, 2),
            'Paid',
            $payment->verifiedBy->name ?? 'N/A',
            $payment->note ?? ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Daily Collection';
    }
}
