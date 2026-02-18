<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detain Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        .header p {
            margin: 3px 0;
            font-size: 9px;
            color: #666;
        }
        .filters {
            background-color: #f5f5f5;
            padding: 8px;
            margin-bottom: 12px;
            border-radius: 3px;
        }
        .filters h3 {
            margin: 0 0 6px 0;
            font-size: 10px;
            color: #333;
        }
        .filter-item {
            display: inline-block;
            margin-right: 12px;
            margin-bottom: 4px;
            font-size: 8px;
        }
        .filter-label {
            font-weight: bold;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th {
            background-color: #34495e;
            color: white;
            padding: 6px 3px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
        }
        td {
            padding: 5px 3px;
            border-bottom: 1px solid #ddd;
            font-size: 7px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 7px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .summary {
            background-color: #e8f4f8;
            padding: 8px;
            margin-bottom: 12px;
            border-radius: 3px;
        }
        .summary-item {
            display: inline-block;
            margin-right: 15px;
            font-size: 9px;
        }
        .summary-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .summary-value {
            color: #3498db;
            font-weight: bold;
        }
        .status-detained {
            color: #e74c3c;
            font-weight: bold;
        }
        .status-released {
            color: #27ae60;
            font-weight: bold;
        }
        .detain-type {
            background-color: #e74c3c;
            color: white;
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
        }
        .entity-level {
            background-color: #3498db;
            color: white;
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 7px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Detain Report</h1>
        <p>Containers and cargo held beyond allowed free period</p>
        <p>Generated on: {{ $generated_at }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <h3>Applied Filters:</h3>
        @if(isset($filters['date_from']) || isset($filters['date_to']))
            <div class="filter-item">
                <span class="filter-label">Date Range:</span>
                {{ $filters['date_from'] ?? 'Any' }} to {{ $filters['date_to'] ?? 'Any' }}
            </div>
        @endif
        @if(isset($filters['status']))
            <div class="filter-item">
                <span class="filter-label">Status:</span> {{ ucfirst($filters['status']) }}
            </div>
        @endif
        @if(isset($filters['detain_type']))
            <div class="filter-item">
                <span class="filter-label">Detain Type:</span> {{ $filters['detain_type'] }}
            </div>
        @endif
        @if(isset($filters['entity_level']))
            <div class="filter-item">
                <span class="filter-label">Entity Level:</span> {{ ucfirst($filters['entity_level']) }}
            </div>
        @endif
    </div>
    @endif

    <div class="summary">
        <div class="summary-item">
            <span class="summary-label">Total Records:</span>
            <span class="summary-value">{{ $records->count() }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Detained:</span>
            <span class="summary-value" style="color: #e74c3c;">{{ $records->where('status', 'Detained')->count() }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Released:</span>
            <span class="summary-value" style="color: #27ae60;">{{ $records->where('status', 'Released')->count() }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">ID</th>
                <th style="width: 10%;">Shipment Ref</th>
                <th style="width: 10%;">HBL Ref</th>
                <th style="width: 7%;">Level</th>
                <th style="width: 6%;">Type</th>
                <th style="width: 15%;">Detain Reason</th>
                <th style="width: 10%;">Detained Date</th>
                <th style="width: 7%;">Duration</th>
                <th style="width: 7%;">Status</th>
                <th style="width: 10%;">Released Date</th>
                <th style="width: 7%;">Detained By</th>
                <th style="width: 7%;">Lifted By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td>#{{ $record['id'] }}</td>
                <td>{{ $record['shipment_reference'] ?? 'N/A' }}</td>
                <td>{{ $record['hbl_reference'] ?? 'N/A' }}</td>
                <td><span class="entity-level">{{ $record['entity_level'] ?? 'N/A' }}</span></td>
                <td><span class="detain-type">{{ $record['detain_type'] ?? 'N/A' }}</span></td>
                <td>{{ \Illuminate\Support\Str::limit($record['detain_reason'] ?? 'N/A', 40) }}</td>
                <td>{{ $record['detained_date'] ? \Carbon\Carbon::parse($record['detained_date'])->format('Y-m-d H:i') : 'N/A' }}</td>
                <td style="font-weight: bold;">{{ $record['detention_duration_human'] ?? 'N/A' }}</td>
                <td>
                    <span class="{{ $record['status'] === 'Detained' ? 'status-detained' : 'status-released' }}">
                        {{ $record['status'] ?? 'N/A' }}
                    </span>
                </td>
                <td>{{ $record['released_date'] ? \Carbon\Carbon::parse($record['released_date'])->format('Y-m-d H:i') : '-' }}</td>
                <td>{{ $record['detained_by']['name'] ?? 'N/A' }}</td>
                <td>{{ $record['lifted_by']['name'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Showing {{ $records->count() }} records | Page 1 of 1</p>
    </div>
</body>
</html>
