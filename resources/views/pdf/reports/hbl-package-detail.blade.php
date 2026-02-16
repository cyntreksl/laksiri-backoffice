<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>HBL Package Details - {{ $hbl->hbl_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .header h1 {
            font-size: 20px;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 9px;
            color: #666;
        }
        
        .hbl-info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f0f4ff;
            border-left: 4px solid #667eea;
            border-radius: 4px;
        }
        
        .hbl-info h2 {
            font-size: 14px;
            color: #667eea;
            margin-bottom: 8px;
        }
        
        .hbl-info-grid {
            display: table;
            width: 100%;
        }
        
        .hbl-info-row {
            display: table-row;
        }
        
        .hbl-info-cell {
            display: table-cell;
            padding: 3px 10px 3px 0;
            font-size: 9px;
        }
        
        .hbl-info-cell strong {
            color: #555;
        }
        
        .stats-section {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
        }
        
        .stats-item {
            display: table-cell;
            text-align: center;
            padding: 5px;
        }
        
        .stats-item .label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
        }
        
        .stats-item .value {
            font-size: 14px;
            font-weight: bold;
            color: #667eea;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th {
            background-color: #667eea;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }
        
        td {
            padding: 6px 5px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 9px;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>HBL Package Details</h1>
        <p>Generated on: {{ $generated_at }}</p>
    </div>

    <div class="hbl-info">
        <h2>HBL Information</h2>
        <div class="hbl-info-grid">
            <div class="hbl-info-row">
                <div class="hbl-info-cell"><strong>HBL Number:</strong> {{ $hbl->hbl_number }}</div>
                <div class="hbl-info-cell"><strong>Cargo Type:</strong> {{ $hbl->cargo_type }}</div>
            </div>
            <div class="hbl-info-row">
                <div class="hbl-info-cell"><strong>Customer:</strong> {{ $hbl->hbl_name }}</div>
                <div class="hbl-info-cell"><strong>Contact:</strong> {{ $hbl->contact_number }}</div>
            </div>
            <div class="hbl-info-row">
                <div class="hbl-info-cell"><strong>Email:</strong> {{ $hbl->email }}</div>
                <div class="hbl-info-cell"><strong>Branch:</strong> {{ $hbl->branch->name ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="stats-section">
        <div class="stats-grid">
            <div class="stats-item">
                <div class="label">Total Packages</div>
                <div class="value">{{ $packages->count() }}</div>
            </div>
            <div class="stats-item">
                <div class="label">Total Weight (KG)</div>
                <div class="value">{{ number_format($packages->sum('weight'), 2) }}</div>
            </div>
            <div class="stats-item">
                <div class="label">Total CBM</div>
                <div class="value">{{ number_format($packages->sum('volume'), 2) }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Package #</th>
                <th style="width: 20%;">Description</th>
                <th style="width: 10%;">Weight (KG)</th>
                <th style="width: 8%;">CBM</th>
                <th style="width: 15%;">Loaded Date</th>
                <th style="width: 15%;">Unloaded Date</th>
                <th style="width: 20%;">Container</th>
            </tr>
        </thead>
        <tbody>
            @foreach($packages as $package)
            <tr>
                <td>PKG-{{ str_pad($package->id, 6, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $package->remarks ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($package->weight ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($package->volume ?? 0, 2) }}</td>
                <td>{{ $package->loaded_at ? $package->loaded_at->format('Y-m-d H:i') : 'N/A' }}</td>
                <td>{{ $package->unloaded_at ? $package->unloaded_at->format('Y-m-d H:i') : 'N/A' }}</td>
                <td>{{ $package->containers->first()->reference ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>HBL: {{ $hbl->hbl_number }} | Total Packages: {{ $packages->count() }}</p>
    </div>
</body>
</html>
