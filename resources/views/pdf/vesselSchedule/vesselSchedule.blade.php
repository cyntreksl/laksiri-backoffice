<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Vessel Schedule</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            margin: 15px;
        }
        /* Updated header styling */
        .header {
            width: 100%;
            margin-bottom: 20px;
            position: relative;
            height: 30px; /* Fixed height to ensure proper alignment */
        }
        .header h1 {
            font-size: 14pt;
            margin: 0;
            font-weight: bold;
            font-style: italic;
            position: absolute;
            left: 0;
        }
        .date {
            font-size: 14pt;
            font-weight: bold;
            font-style: italic;
            position: absolute;
            right: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th {
            font-size: 10pt;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
        }
        td {
            font-size: 9pt;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }
        .note {
            margin-top: 20px;
            font-size: 11pt;
            font-style: italic;
        }
        .underline {
            text-decoration: underline;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
@php
    // Initialize variables for pagination
    $containersPerPage = 13;
    $containerCount = count($containers ?? []);
    $pageCount = ceil($containerCount / $containersPerPage);
    $serial_number = 1;
@endphp

@for($page = 0; $page < $pageCount; $page++)
    @if($page > 0)
        <div class="page-break"></div>
    @endif

    <div class="header">
        <h1>VESSEL SCHEDULE</h1>
        <div class="date">{{ $formattedDate }}</div>
    </div>

    <table>
        <thead>
        <tr>
            <th width="5%"></th>
            <th width="15%">VESSEL NAME</th>
            <th width="15%">AGENT / COUNTRY</th>
            <th width="12%">CON. NO.</th>
            <th width="15%">DOC / BL</th>
            <th width="8%">RELEASE</th>
            <th width="8%">QTY</th>
            <th width="10%">SHIP. AGENT</th>
            <th width="12%">DATE</th>
        </tr>
        </thead>
        <tbody>
        @php
            // Get containers for current page
            $pageContainers = array_slice($containers ?? [], $page * $containersPerPage, $containersPerPage);
        @endphp

        @foreach($pageContainers as $container)
            <tr>
                <td>{{ sprintf('%02d', $serial_number++) }}.</td>
                <td>{{ $container['vessel_name'] }}</td>
                <td>{{ $container['agent'] }}</td>
                <td>{{ $container['container_number'] ?? '' }}</td>
                <td>
                    <span class="underline">{{ $container['bl_number'] ?? '' }}</span><br>
                    {{ $container['status'] ?? 'YES/NO' }}
                </td>
                <td>{{ $container['release'] }}</td>
                <td>{{ $container['quantity'] }}</td>
                <td>{{ $container['ship_agent'] }}</td>
                <td>{{ $container['date'] ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="note">
        NOTE : VESSEL ARRIVE DATE SUBJECT TO CHANGE
    </div>
@endfor
</body>
</html>
