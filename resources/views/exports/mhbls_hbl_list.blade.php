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
    $total_nototal = 0;
    $total_vtotal = 0;
    $total_gtotal = 0;
@endphp
@foreach($data as $item)
    @php
        $total_gtotal += $item[1];
        $total_vtotal += $item[2];
        $total_nototal += $item[3];
    @endphp
@endforeach

@foreach ($chunks as $chunkIndex => $chunk)
    <table class="hbl-content">
        <thead style="padding: 0; margin: 0">
        <tr style="font-family: 'Times New Roman',fantasy; font-size: 14px; background-color: #D8D8D8  ;">
            <th colspan="5" style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px; height: 2%">MHBL {{$mhbl->hbl_number}}</th>
        </tr>
        <tr style="font-family: 'Times New Roman',fantasy; font-size: 14px; background-color: #D8D8D8  ;">
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">SR <br>NO</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">HBL</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">WEIGHT</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;"> VOLUME</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">NO OF PACKAGE</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($chunk as $index => $item)
            <tr>
                <td style="font-size: 11px; text-align: center; height: 2%">{{ $serialNumber++ }}</td>
                <td style="font-size: 11px; text-align: center; height: 2%">{{ $item[0] }}</td>
                <td style="font-size: 11px; text-align: left; height: 2%">{{ $item[1] }}</td>
                <td style="font-size: 11px; text-align: center; height: 2%">{{ $item[2] }}</td>
                <td style="font-size: 11px; text-align: center; height: 2%">{{ $item[3] }}</td>
            </tr>
        @endforeach

        @if ($loop->last)
            <tr style="border: none; line-height: 20px !important; font-size: 12px;">
                <td colspan="2" style="border: none; text-align: right;"></td>
                <td style="border-top: none; border-right: none; border-left: none; border-bottom: double;text-align: right;"><strong>{{ number_format($total_gtotal, 0) }}</strong></td>
                <td style="border-top: none; border-right: none; border-left: none; border-bottom: double;text-align: right;"><strong>{{ number_format($total_vtotal, 2) }}</strong></td>
                <td style="border-top: none; border-right: none; border-left: none; border-bottom: double;text-align: right;"><strong>{{ number_format($total_nototal, 2) }}</strong></td>
            </tr>
        @endif

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
