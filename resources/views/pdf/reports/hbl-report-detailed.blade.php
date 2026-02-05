<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HBL Report - Detailed</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8px;
            color: #333;
            line-height: 1.3;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 3px solid #2c3e50;
        }

        .header h1 {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header .company-name {
            font-size: 16px;
            color: #34495e;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .header .report-info {
            font-size: 7px;
            color: #7f8c8d;
            margin-top: 4px;
        }

        .logo {
            max-height: 45px;
            margin-bottom: 5px;
        }

        .summary-section {
            background: #667eea;
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 5px;
            color: white;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-item {
            display: table-cell;
            width: 20%;
            text-align: center;
            padding: 5px;
            border-right: 1px solid rgba(255,255,255,0.3);
        }

        .summary-item:last-child {
            border-right: none;
        }

        .summary-label {
            font-size: 7px;
            text-transform: uppercase;
            margin-bottom: 3px;
            opacity: 0.9;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
        }

        .filters-section {
            background-color: #f8f9fa;
            padding: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #3498db;
            border-radius: 3px;
        }

        .filters-title {
            font-size: 9px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .filter-item {
            display: inline-block;
            margin-right: 12px;
            margin-bottom: 3px;
            font-size: 7px;
            background-color: white;
            padding: 2px 6px;
            border-radius: 3px;
            border: 1px solid #e0e0e0;
        }

        .filter-label {
            font-weight: bold;
            color: #34495e;
        }

        .filter-value {
            color: #3498db;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .data-table thead {
            background: #34495e;
            color: white;
        }

        .data-table th {
            padding: 5px 3px;
            text-align: left;
            font-size: 7px;
            font-weight: bold;
            border: 1px solid #2c3e50;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 4px 3px;
            border: 1px solid #ddd;
            font-size: 7px;
            vertical-align: top;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .data-table tbody tr:hover {
            background-color: #e3f2fd;
        }

        .data-table tfoot {
            background-color: #34495e;
            color: white;
            font-weight: bold;
        }

        .data-table tfoot td {
            border-color: #2c3e50;
            padding: 6px 3px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 6px;
            font-weight: bold;
            text-transform: uppercase;
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
            background-color: #27ae60;
            color: white;
        }

        .badge-d2d {
            background-color: #e67e22;
            color: white;
        }

        .badge-upb {
            background-color: #16a085;
            color: white;
        }

        .badge-gift {
            background-color: #f39c12;
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

        .badge-released {
            background-color: #27ae60;
            color: white;
        }

        .badge-active {
            background-color: #3498db;
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
            border-top: 2px solid #34495e;
            background-color: #f8f9fa;
        }

        .page-break {
            page-break-after: always;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            font-size: 11px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .status-icon {
            font-weight: bold;
            margin-right: 2px;
            font-size: 8px;
        }

        .status-detained {
            color: #e74c3c;
        }

        .status-short {
            color: #f39c12;
        }

        .info-line {
            font-size: 6px;
            color: #7f8c8d;
            margin-top: 1px;
        }

        .amount-positive {
            color: #27ae60;
        }

        .amount-negative {
            color: #e74c3c;
        }

        .warehouse-colombo {
            background-color: #3498db;
            color: white;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 6px;
        }

        .warehouse-nintavur {
            background-color: #e74c3c;
            color: white;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 6px;
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
            Generated: {{ $generatedAt }} | By: {{ $generatedBy }} | Total Records: {{ $hbls->count() }}
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
                <div class="summary-label">Grand Total</div>
                <div class="summary-value">{{ number_format($stats['total_amount'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Paid</div>
                <div class="summary-value">{{ number_format($stats['total_paid'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Balance Due</div>
                <div class="summary-value">{{ number_format($stats['total_balance'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    @if(count($filters) > 0)
    <div class="filters-section">
        <div class="filters-title">📋 Applied Filters</div>
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
                <th style="width: 4%;">HBL No</th>
                <th style="width: 4%;">Ref</th>
                <th style="width: 9%;">Customer Info</th>
                <th style="width: 8%;">Consignee Info</th>
                <th style="width: 5%;">Branch</th>
                <th style="width: 4%;">WH</th>
                <th style="width: 4%;">Cargo</th>
                <th style="width: 4%;">Type</th>
                <th style="width: 3%;">Pkgs</th>
                <th style="width: 5%;">Loaded</th>
                <th style="width: 5%;">Unloaded</th>
                <th style="width: 4%;">Freight</th>
                <th style="width: 4%;">Bill</th>
                <th style="width: 4%;">DO</th>
                <th style="width: 4%;">Dest</th>
                <th style="width: 4%;">Discount</th>
                <th style="width: 5%;">Total</th>
                <th style="width: 5%;">Paid</th>
                <th style="width: 5%;">Balance</th>
                <th style="width: 4%;">Status</th>
                <th style="width: 5%;">Created</th>
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
                    <strong>{{ $hbl->hbl_number ?? $hbl->hbl ?? 'N/A' }}</strong>
                </td>
                <td>{{ $hbl->reference ?? '-' }}</td>
                <td>
                    <strong>{{ $hbl->hbl_name ?? 'N/A' }}</strong>
                    @if($hbl->contact_number)
                        <div class="info-line">📞 {{ $hbl->contact_number }}</div>
                    @endif
                    @if($hbl->email)
                        <div class="info-line">✉ {{ $hbl->email }}</div>
                    @endif
                </td>
                <td>
                    <strong>{{ $hbl->consignee_name ?? 'N/A' }}</strong>
                    @if($hbl->consignee_contact)
                        <div class="info-line">📞 {{ $hbl->consignee_contact }}</div>
                    @endif
                    @if($hbl->consignee_nic)
                        <div class="info-line">🆔 {{ $hbl->consignee_nic }}</div>
                    @endif
                </td>
                <td>{{ $hbl->branch?->name ?? 'N/A' }}</td>
                <td>
                    @if($hbl->warehouse == 'COLOMBO')
                        <span class="warehouse-colombo">CLB</span>
                    @elseif($hbl->warehouse == 'NINTAVUR')
                        <span class="warehouse-nintavur">NTV</span>
                    @else
                        {{ $hbl->warehouse ?? '-' }}
                    @endif
                </td>
                <td>
                    @if($hbl->cargo_type == 'Sea Cargo')
                        <span class="badge badge-sea">🚢 Sea</span>
                    @elseif($hbl->cargo_type == 'Air Cargo')
                        <span class="badge badge-air">✈ Air</span>
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
                    @elseif($hbl->hbl_type == 'UPB')
                        <span class="badge badge-upb">UPB</span>
                    @elseif($hbl->hbl_type == 'Gift')
                        <span class="badge badge-gift">Gift</span>
                    @else
                        {{ $hbl->hbl_type }}
                    @endif
                </td>
                <td class="text-center"><strong>{{ $hbl->packages->count() }}</strong></td>
                <td>{{ $loadedDate ? \Carbon\Carbon::parse($loadedDate)->format('Y-m-d') : '-' }}</td>
                <td>{{ $unloadedDate ? \Carbon\Carbon::parse($unloadedDate)->format('Y-m-d') : '-' }}</td>
                <td class="text-right">{{ number_format($hbl->freight_charge ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($hbl->bill_charge ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($hbl->do_charge ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($hbl->destination_charge ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($hbl->discount ?? 0, 2) }}</td>
                <td class="text-right"><strong>{{ number_format($hbl->grand_total, 2) }}</strong></td>
                <td class="text-right amount-positive">{{ number_format($hbl->paid_amount, 2) }}</td>
                <td class="text-right {{ $balance > 0 ? 'amount-negative' : 'amount-positive' }}">
                    <strong>{{ number_format($balance, 2) }}</strong>
                </td>
                <td>
                    @if($isDetained)
                        <span class="badge badge-detained">🔒 Detained</span>
                    @elseif($isShortLoaded)
                        <span class="badge badge-short-loaded">⚠ Short</span>
                    @elseif($hbl->is_released)
                        <span class="badge badge-released">✓ Released</span>
                    @else
                        <span class="badge badge-active">Active</span>
                    @endif
                </td>
                <td>
                    {{ $hbl->created_at ? $hbl->created_at->format('Y-m-d') : '-' }}
                    @if($hbl->user)
                        <div class="info-line">By: {{ $hbl->user->name }}</div>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" class="text-right"><strong>GRAND TOTALS:</strong></td>
                <td class="text-center"><strong>{{ number_format($stats['total_packages']) }}</strong></td>
                <td colspan="7"></td>
                <td class="text-right"><strong>{{ number_format($stats['total_amount'], 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($stats['total_paid'], 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($stats['total_balance'], 2) }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">
        <div style="font-size: 30px; margin-bottom: 10px;">📭</div>
        <strong>No HBL records found</strong>
        <div style="margin-top: 5px;">No records match the selected filter criteria.</div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div><strong>{{ $companyName }}</strong> - House Bill of Lading Report</div>
        <div>This is a computer-generated document. No signature is required. | Page <span class="pagenum"></span></div>
    </div>
</body>
</html>
