<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Loading Tally Sheet </title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Times New Roman, serif;
        }

        th, td {
            border: 1px solid black;
            padding: 2px;
            text-align: left;
        }

        .hbl-content tr{
            line-height: 10px;
        }

        .page-break {
            page-break-after: always;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 12px;
            font-family: 'Times New Roman', serif;
            left: 0; /* Add this line to ensure the footer starts from the left edge */
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 12px;
            font-family: 'Times New Roman', serif;
            left: 0;
            display: flex; /* Use flexbox for layout */
            justify-content: space-between; /* Distribute space between elements */
            align-items: center; /* Vertically center items */
            padding: 0 20px; /* Add some padding to the sides */
        }

        .footer-text {
            text-align: left;
        }

        .page-number {
            text-align: center; /* Center the page number */
        }

        .page-number:after {
            content: counter(page);
        }
    </style>
</head>
<body>

@php
    $serialNumber = 1;
    $itemsPerPage = 20;

    $chunks = array_chunk($data, $itemsPerPage);
@endphp

@foreach ($chunks as $chunkIndex => $chunk)
    <table class="hbl-content">
        <thead style="padding: 0; margin: 0">
        <tr style="font-family: 'Times New Roman',fantasy; font-size: 14px; background-color: #D8D8D8  ;">
            <th colspan="16" style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px; height: 3%">LOADING TALLY SHEET</th>
        </tr>
        <tr style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
            <th colspan="3" style="text-align: left; font-family: 'Times New Roman',fantasy; font-size: 10px; height: 3%">CONTR NO: {{$container?->container_number}}</th>
            <th colspan="11" style="text-align: left; font-family: 'Times New Roman',fantasy; font-size: 10px; height: 3%">DATE LOADED: {{ \Carbon\Carbon::parse($container?->loading_started_at)->format('Y-m-d') }} </th>
            <th colspan="2" style="text-align: left; font-family: 'Times New Roman',fantasy; font-size: 10px; height: 3%">SHIPMENT NO: {{$container?->reference}}</th>
        </tr>
        <tr style="font-family: 'Times New Roman',fantasy; font-size: 14px; background-color: #D8D8D8  ;">
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">SN</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">HBL</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">NAME OF CUSTOMER</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;"> CBM</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">TOT</th>
            <th colspan="9" style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">TYPE OF PACKAGE</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">DESTINATION</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">REMARKS</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($chunk as $index => $item)
            <tr>
                <td style="font-size: 11px; text-align: center; height: 3%">{{ $serialNumber++ }}</td>
                <td style="font-size: 11px; text-align: center; height: 3%">{{ $item[0] }}</td>
                <td style="font-size: 11px; text-align: left; height: 3%">{{ strtoupper($item[1]) }}</td>
                <td style="font-size: 11px; text-align: center; height: 3%">{{ $item[2] }}</td>
                <td style="font-size: 11px; text-align: center; height: 3%">{{ $item[3] }}</td>
                <td style="font-size: 11px; width: 4%; text-align: center; height: 3%">{{ $item[4][0] ?? '' }}</td>
                <td style="font-size: 11px; width: 4%; text-align: center; height: 3%">{{ $item[4][1] ?? ''  }}</td>
                <td style="font-size: 11px; width: 4%; text-align: center; height: 3%">{{ $item[4][2] ?? ''  }}</td>
                <td style="font-size: 11px; width: 4%; text-align: center; height: 3%">{{ $item[4][3] ?? ''  }}</td>
                <td style="font-size: 11px; width: 4%; text-align: center; height: 3%">{{ $item[4][4] ?? ''  }}</td>
                <td style="font-size: 11px; width: 4%; text-align: center; height: 3%">{{ $item[4][5] ?? ''  }}</td>
                <td style="font-size: 11px; width: 4%; text-align: center; height: 3%">{{ $item[4][6] ?? ''  }}</td>
                <td style="font-size: 11px; width: 4%; text-align: center; height: 3%">{{ $item[4][7] ?? ''  }}</td>
                <td style="font-size: 11px; width: 4%; text-align: center; height: 3%">{{ $item[4][8] ?? ''  }}</td>
                <td style="font-size: 11px; text-align: center; height: 3%">{{ $item[5] }}</td>
                <td style="font-size: 11px; text-align: center; height: 3%">{{ $item[6] }}</td>
            </tr>


        @endforeach

        </tbody>
    </table>
    <div class="footer">
        <span class="page-number">Page: </span>
    </div>
    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
