<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Shipment Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            font-size: 10px;
            color: #666;
        }
        .filters {
            background-color: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .filters h3 {
            margin: 0 0 8px 0;
            font-size: 11px;
            color: #333;
        }
        .filter-item {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 5px;
            font-size: 9px;
        }
        .filter-label {
            font-weight: bold;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #34495e;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }
        td {
            padding: 6px 4px;
            border-bottom: 1px solid #ddd;
            font-size: 8px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .summary {
            background-color: #e8f4f8;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .summary-item {
            display: inline-block;
            margin-right: 20px;
            font-size: 10px;
        }
        .summary-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .summary-value {
            color: #3498db;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Shipment Report</h1>
        <p>Generated on: {{ $generated_at }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <h3>Applied Filters:</h3>
        @if(isset($filters['loaded_date_from']) || isset($filters['loaded_date_to']))
            <div class="filter-item">
                <span class="filter-label">Loaded Date:</span>
                {{ $filters['loaded_date_from'] ?? 'Any' }} to {{ $filters['loaded_date_to'] ?? 'Any' }}
            </div>
        @endif
        @if(isset($filters['unloaded_date_from']) || isset($filters['unloaded_date_to']))
            <div class="filter-item">
                <span class="filter-label">Unloaded Date:</span>
                {{ $filters['unloaded_date_from'] ?? 'Any' }} to {{ $filters['unloaded_date_to'] ?? 'Any' }}
            </div>
        @endif
        @if(isset($filters['reached_date_from']) || isset($filters['reached_date_to']))
            <div class="filter-item">
                <span class="filter-label">Reached Date:</span>
                {{ $filters['reached_date_from'] ?? 'Any' }} to {{ $filters['reached_date_to'] ?? 'Any' }}
            </div>
        @endif
        @if(isset($filters['arrival_date_from']) || isset($filters['arrival_date_to']))
            <div class="filter-item">
                <span class="filter-label">Arrival Date:</span>
                {{ $filters['arrival_date_from'] ?? 'Any' }} to {{ $filters['arrival_date_to'] ?? 'Any' }}
            </div>
        @endif
        @if(isset($filters['release_date_from']) || isset($filters['release_date_to']))
            <div class="filter-item">
                <span class="filter-label">Release Date:</span>
                {{ $filters['release_date_from'] ?? 'Any' }} to {{ $filters['release_date_to'] ?? 'Any' }}
            </div>
        @endif
        @if(isset($filters['cargo_type']))
            <div class="filter-item">
                <span class="filter-label">Cargo Type:</span> {{ $filters['cargo_type'] }}
            </div>
        @endif
        @if(isset($filters['status']))
            <div class="filter-item">
                <span class="filter-label">Container Status:</span> {{ $filters['status'] }}
            </div>
        @endif
    </div>
    @endif

    <div class="summary">
        <div class="summary-item">
            <span class="summary-label">Total Shipments:</span>
            <span class="summary-value">{{ $containers->count() }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Reference</th>
                <th style="width: 10%;">Cargo Type</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 12%;">Container No.</th>
                <th style="width: 10%;">BL/AWB</th>
                <th style="width: 12%;">Branch</th>
                <th style="width: 10%;">Loaded</th>
                <th style="width: 10%;">Unloaded</th>
                <th style="width: 8%;">Reached</th>
                <th style="width: 6%;">Pkgs</th>
            </tr>
        </thead>
        <tbody>
            @foreach($containers as $container)
            <tr>
                <td>{{ $container->reference ?? 'N/A' }}</td>
                <td>{{ $container->cargo_type ?? 'N/A' }}</td>
                <td>{{ $container->status ?? 'N/A' }}</td>
                <td>{{ $container->container_number ?? '-' }}</td>
                <td>{{ $container->bl_number ?: $container->awb_number ?: '-' }}</td>
                <td>{{ $container->branch?->name ?? 'N/A' }}</td>
                <td>{{ $container->loading_started_at ? $container->loading_started_at->format('Y-m-d') : '-' }}</td>
                <td>{{ $container->unloading_started_at ? $container->unloading_started_at->format('Y-m-d') : '-' }}</td>
                <td>{{ $container->reached_date ? $container->reached_date->format('Y-m-d') : '-' }}</td>
                <td style="text-align: center;">
                    {{ \DB::table('container_hbl_package')->where('container_id', $container->id)->count() }}
                </td>
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
