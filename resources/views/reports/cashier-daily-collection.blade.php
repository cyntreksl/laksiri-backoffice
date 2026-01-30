<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cashier Daily Collection Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .summary-label {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .total-row {
            font-weight: bold;
            background-color: #e8f5e9 !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Cashier Daily Collection Report</h1>
        <p>Period: {{ $summary['date_from'] }} to {{ $summary['date_to'] }}</p>
        <p>Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Total Transactions:</span>
            <span>{{ $summary['total_transactions'] }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Collected Amount:</span>
            <span>LKR {{ number_format($summary['total_collected'], 2) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Receipt No</th>
                <th>Invoice No</th>
                <th>HBL Reference</th>
                <th>Customer</th>
                <th>Contact</th>
                <th class="text-right">Amount (LKR)</th>
                <th>Status</th>
                <th>Collected By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $payment->receipt_number ?? 'N/A' }}</td>
                <td>{{ $payment->invoice_number ?? 'N/A' }}</td>
                <td>{{ $payment->token->reference ?? 'N/A' }}</td>
                <td>{{ $payment->token->customer->name ?? 'N/A' }}</td>
                <td>{{ $payment->token->customer->contact ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($payment->paid_amount, 2) }}</td>
                <td>Paid</td>
                <td>{{ $payment->verifiedBy->name ?? 'N/A' }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>TOTAL:</strong></td>
                <td class="text-right"><strong>{{ number_format($summary['total_collected'], 2) }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated report. No signature required.</p>
    </div>
</body>
</html>
