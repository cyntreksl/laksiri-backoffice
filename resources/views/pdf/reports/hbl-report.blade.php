<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HBL Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2c3e50;
        }

        .header h1 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .header .company-name {
            font-size: 14px;
            color: #34495e;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .header .report-info {
            font-size: 8px;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .logo {
            max-height: 40px;
            margin-bottom: 5px;
        }

        .summary-section {
            background-color: #ecf0f1;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 5px;
        }

        .summary-label {
            font-size: 8px;
            color: #7f8c8d;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .summary-value {
            font-size: 12px;
            font-weight: bold;
            color: #2c3e50;
        }

        .filters-section {
            background-color: #f8f9fa;
            padding: 8px;
            margin-bottom: 10px;
            border-left: 3px solid #3498db;
        }

        .filters-title {
            font-size: 10px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .filter-item {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 3px;
            font-size: 8px;
        }

        .filter-label {
            font-weight: bold;
            color: #34495e;
        }

        .filter-value {
            color: #7f8c8d;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .data-table thead {
            background-color: #34495e;
            color: white;
        }

        .data-table th {
            padding: 6px 4px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            border: 1px solid #2c3e50;
        }

        .data-table td {
            padding: 5px 4px;
            border: 1px solid #ddd;
            font-size: 8px;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .data-table tbody tr:hover {
            background-color: #e8f4f8;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }

        .badge-sea {
            background-color: #3498db;
            color: white;
        }

        .badge-air {
            background-color: #9b59b6;
            color: white;
        }

        .badge-normal {
            background-color: #2ecc71;
            color: white;
        }

        .badge-d2d {
            background-color: #e67e22;
            color: white;
        }

        .badge-third-party {
            background-color: #95a5a6;
            color: white;
        }

        .badge-detained {
            background-color: #e74c3c;
            color: white;
        }

        .badge-short-loaded {
            background-color: #f39c12;
            color: white;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7px;
            color: #7f8c8d;
            padding: 5px;
            border-top: 1px solid #ddd;
        }

        .page-break {
            page-break-after: always;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
            font-size: 10px;
        }

        .status-icon {
            font-weight: bold;
            margin-right: 2px;
        }

        .status-detained {
            color: #e74c3c;
        }

        .status-short {
            color: #f39c12;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        @if($logoPath)
            <img src="{{ $logoPath }}" alt="Logo" class="logo">
        @endif
        <div class="company-name">{{ $companyName }}</div>
        <h1>HBL Report</h1>
        <div class="report-info">
            Generated on: {{ $generatedAt }} | Generated by: {{ $generatedBy }}
        </div>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total HBLs</div>
                <div class="summary-value">{{ number_format($stats['total_hbls']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Packages</div>
                <div class="summary-value">{{ number_format($stats['total_packages']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Amount</div>
                <div class="summary-value">{{ number_format($stats['total_amount'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Paid</div>
                <div class="summary-value">{{ number_format($stats['total_paid'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    @if(count($filters) > 0)
    <div class="filters-section">
        <div class="filters-title">Applied Filters:</div>
        @foreach($filters as $label => $value)
            <div class="filter-item">
                <span class="filter-label">{{ $label }}:</span>
                <span class="filter-value">{{ $value }}</span>
            </div>
        @endforeach
    </div>
    @endif

    <!-- Data Table -->
    @if($hbls->count() > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">HBL No</th>
                <th style="width: 5%;">Ref</th>
                <th style="width: 10%;">Customer</th>
                <th style="width: 7%;">Contact</th>
                <th style="width: 8%;">Consignee</th>
                <th style="width: 6%;">Branch</th>
                <th style="width: 5%;">Warehouse</th>
                <th style="width: 5%;">Cargo</th>
                <th style="width: 5%;">Type</th>
                <th style="width: 3%;">Pkgs</th>
                <th style="width: 6%;">Loaded</th>
                <th style="width: 6%;">Unloaded</th>
                <th style="width: 5%;">Freight</th>
                <th style="width: 5%;">DO Charge</th>
                <th style="width: 5%;">Total</th>
                <th style="width: 5%;">Paid</th>
                <th style="width: 5%;">Balance</th>
                <th style="width: 4%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hbls as $hbl)
            @php
                $loadedDate = $hbl->packages()->whereNotNull('loaded_at')->orderBy('loaded_at', 'asc')->value('loaded_at');
                $unloadedDate = $hbl->packages()->whereNotNull('unloaded_at')->orderBy('unloaded_at', 'desc')->value('unloaded_at');
                $balance = $hbl->grand_total - $hbl->paid_amount;
                $isDetained = $hbl->latestDetainRecord?->is_rtf ?? false;
                $isShortLoaded = $hbl->is_short_loading ?? false;
            @endphp
            <tr>
                <td>
                    @if($isDetained)
                        <span class="status-icon status-detained">🔒</span>
                    @endif
                    @if($isShortLoaded)
                        <span class="status-icon status-short">⚠</span>
                    @endif
                    {{ $hbl->hbl_number ?? $hbl->hbl ?? 'N/A' }}
                </td>
                <td>{{ $hbl->reference ?? '-' }}</td>
                <td>
                    {{ $hbl->hbl_name ?? 'N/A' }}
                    @if($hbl->email)
                        <br><span style="font-size: 7px; color: #7f8c8d;">{{ $hbl->email }}</span>
                    @endif
                </td>
                <td>{{ $hbl->contact_number ?? 'N/A' }}</td>
                <td>
                    {{ $hbl->consignee_name ?? 'N/A' }}
                    @if($hbl->consignee_contact)
                        <br><span style="font-size: 7px; color: #7f8c8d;">{{ $hbl->consignee_contact }}</span>
                    @endif
                </td>
                <td>{{ $hbl->branch?->name ?? 'N/A' }}</td>
                <td>{{ $hbl->warehouse ?? '-' }}</td>
                <td>
                    @if($hbl->cargo_type == 'Sea Cargo')
                        <span class="badge badge-sea">SEA</span>
                    @elseif($hbl->cargo_type == 'Air Cargo')
                        <span class="badge badge-air">AIR</span>
                    @else
                        {{ $hbl->cargo_type }}
                    @endif
                </td>
                <td>
                    @if($hbl->hbl_type == 'Normal')
                        <span class="badge badge-normal">Normal</span>
                    @elseif($hbl->hbl_type == 'Door to Door')
                        <span class="badge badge-d2d">D2D</span>
                    @elseif($hbl->hbl_type == 'Third Party')
                        <span class="badge badge-third-party">3rd</span>
                    @else
                        {{ $hbl->hbl_type }}
                    @endif
                </td>
                <td class="text-center">{{ $hbl->packages->count() }}</td>
                <td>{{ $loadedDate ? \Carbon\Carbon::parse($loadedDate)->format('Y-m-d') : '-' }}</td>
                <td>{{ $unloadedDate ? \Carbon\Carbon::parse($unloadedDate)->format('Y-m-d') : '-' }}</td>
                <td class="text-right">{{ number_format($hbl->freight_charge ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($hbl->do_charge ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($hbl->grand_total, 2) }}</td>
                <td class="text-right">{{ number_format($hbl->paid_amount, 2) }}</td>
                <td class="text-right">{{ number_format($balance, 2) }}</td>
                <td>
                    @if($isDetained)
                        <span class="badge badge-detained">Detained</span>
                    @elseif($isShortLoaded)
                        <span class="badge badge-short-loaded">Short</span>
                    @elseif($hbl->is_released)
                        <span class="badge badge-normal">Released</span>
                    @else
                        <span class="badge badge-normal">Active</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #ecf0f1; font-weight: bold;">
                <td colspan="9" class="text-right">TOTALS:</td>
                <td class="text-center">{{ number_format($stats['total_packages']) }}</td>
                <td colspan="4"></td>
                <td class="text-right">{{ number_format($stats['total_amount'], 2) }}</td>
                <td class="text-right">{{ number_format($stats['total_paid'], 2) }}</td>
                <td class="text-right">{{ number_format($stats['total_balance'], 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">
        No HBL records found matching the selected criteria.
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div>{{ $companyName }} - HBL Report</div>
        <div>This is a computer-generated document. No signature is required.</div>
    </div>
</body>
</html>
