<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>HBL Package Report</title>
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
        
        .info-section {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        
        .info-section p {
            margin: 3px 0;
            font-size: 9px;
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
        
        .font-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>HBL Package Report</h1>
        <p>Generated on: {{ $generated_at }}</p>
    </div>

    <div class="info-section">
        <p><strong>Total Packages:</strong> {{ $packages->count() }}</p>
        <p><strong>Total Weight:</strong> {{ number_format($packages->sum('weight'), 2) }} KG</p>
        <p><strong>Total CBM:</strong> {{ number_format($packages->sum('volume'), 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Package #</th>
                <th style="width: 12%;">HBL Number</th>
                <th style="width: 15%;">Customer</th>
                <th style="width: 10%;">Cargo Type</th>
                <th style="width: 8%;">Weight (KG)</th>
                <th style="width: 8%;">CBM</th>
                <th style="width: 12%;">Loaded Date</th>
                <th style="width: 12%;">Unloaded Date</th>
                <th style="width: 13%;">Container</th>
            </tr>
        </thead>
        <tbody>
            @foreach($packages as $package)
            <tr>
                <td>PKG-{{ str_pad($package->id, 6, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $package->hbl->hbl_number ?? 'N/A' }}</td>
                <td>{{ $package->hbl->hbl_name ?? 'N/A' }}</td>
                <td>{{ $package->hbl->cargo_type ?? '' }}</td>
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
        <p>Page 1 of 1</p>
    </div>
</body>
</html>
