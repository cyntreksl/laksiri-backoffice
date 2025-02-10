<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> AIR Cargo Manifest </title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Times New Roman, serif;
        }

        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }

        .center-text {
            text-align: center;
        }

        .ship {
            display: flex;
        }

        .hbl {
            margin-right: 20px; /* Adjust as needed */
            display: flex;
            flex-direction: column; /* Stack elements vertically */
        }

        .name {
            display: flex;
            flex-direction: column; /* Stack elements vertically */
            margin-left: 70px; /* Adjust as needed */
            margin-top: 5px;
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
    $itemsPerPage = 6; // Number of rows per page
    $chunks = array_chunk($data, $itemsPerPage); // Split data into chunks of $itemsPerPage
    $total_nototal = 0;
    $total_vtotal = 0;
    $total_gtotal = 0;
@endphp

@foreach($data as $item)
    @foreach ($item[9] as $package)
        @php
            $total_nototal += $package['quantity'];
            $total_vtotal += $package['volume'];
            $total_gtotal += $package['weight'];
        @endphp
    @endforeach
@endforeach
@foreach ($chunks as $chunkIndex => $chunk)
    <table>
        @if ($chunkIndex === 0)
            <thead>
            <tr>
                <th colspan="10" >
                    <strong>
                        OBL    {{$container?->bl_number}}                                                                  UNIVERSAL FREIGHT SERVICES
                        </strong>
                </th>
            </tr>
            <tr>
                <th colspan="10" style="background-color: #D8D8D8 ; text-align: center; ">
                    <strong> <em>  CARGO MANIFEST </em> </strong>
                </th>
            </tr>
            <tr>
                <th colspan="2" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">

                    VESSEL: {{$container?->vessel_name}} <br>

                </th>

                <th colspan="1" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">
                    DATE LOADED:   {{$container?->loading_started_at}} <br>

                </th>

                <th colspan="2" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">
                    DATE:  <br>

                </th>
                <th colspan="2" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">
                    DATE:   <br>

                </th>
                <th colspan="2" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">
                    DATE:   <br>

                </th>
            </tr>

            <tr>
                <th colspan="10" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
                    SHIPPER : {{$settings?->invoice_header_title}}, {{$settings?->invoice_header_address}}.
                    TEL: {{$settings?->invoice_header_telephone}} <br>
                    CONSIGNEE: LAKSIRI SEVA (PVT) LTD. NO: 66, NEW NUGE ROAD, PELIYAGODA, SRI LANKA <br>
                    NOTIFY : LAKSIRI SEVA (PVT) LTD. NO: 31, ST.ANTHONY'S MAWATHA, COLOMBO - 03, SRI LANKA. TEL: +94
                    11-47722800
                </th>
            </tr>

            <tr>
                <th colspan="3" style="height: 3px !important; ">AWB NO {{$container?->awb_number}}</th>
                <th rowspan="2" colspan="1"><p> TOTAL VOLUME: </p></th>
                <th rowspan="2" colspan="3"> {{ number_format($total_vtotal, 2) }}</th>
                <th rowspan="2" colspan="1">TOTAL WEIGHT</th>
                <th rowspan="2" colspan="2"> {{ number_format($total_gtotal, 2) }}</th>

            </tr>
            <tr>
                <th colspan="3"> NO OF PKG: {{ number_format($total_nototal, 0) }}</th>
            </tr>
            @endif
            <tr style="font-family: 'Times New Roman',fantasy; font-size: 14px; background-color: #D8D8D8  ;">
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">SR NO</th>
                <th>HBL NO</th>
                <th> NAME OF SHIPPER</th>
                <th>NAME OF CONSIGNEES</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">TYPE OF PKGS</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">NO.OF PKGS</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">VOLUME CBM</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">GWHT</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">DESCRIPTION OF CARGO</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 11px;">REMARKS</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($chunk as $index => $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td style="border-right:none ;vertical-align: top"> {{ $item[0]}} </td>
                    <td style="border-left:none;vertical-align: top">{{ $item[1]}} {{ $item[2]}} <br>  {{ $item[3]}} <br> {{ $item[4]}}</td>
                    <td style="vertical-align: top">{{ $item[5] }} <br> {{ $item[6] }} <br> {{ $item[7] }} <br> {{ $item[8] }} </td>
                    <td style="vertical-align: top">
                        @foreach ($item[9] as $package)
                            {{ $package['quantity'] }}-{{ $package['package_type'] }}<br>
                        @endforeach
                    </td>
                    <td style="vertical-align: top">
                        @foreach ($item[9] as $package)
                            {{ $package['quantity'] }}<br>
                        @endforeach
                    </td>
                    <td style="vertical-align: top">
                        @foreach ($item[9] as $package)
                            {{ $package['volume'] }}<br>
                        @endforeach
                    </td>
                    <td style="vertical-align: top">
                        @foreach ($item[9] as $package)
                            {{ $package['weight'] }}<br>
                        @endforeach
                    </td>
                    <td>  PERSONAL<br>      EFFECT</td>
                    <td style="text-align: center"><b>     {{$item[10]}}</b></td>

                </tr>
            @endforeach
            @if ($loop->last)
                <tr style="border: none;">
                    <td colspan="5" style="border: none; text-align: right;"></td>
                    <td style="border: none; text-align: center;"><strong><u>{{ number_format($total_nototal, 0) }}</u></strong></td>
                    <td style="border: none; text-align: center;"><strong><u>{{ number_format($total_vtotal, 2) }}</u></strong></td>
                    <td style="border: none; text-align: center;"><strong><u>  {{ number_format($total_gtotal, 2) }}</u></strong></td>
                    <td style="border: none;">&nbsp;</td>
                    <td style="border: none;">&nbsp;</td>
                </tr>

            @endif
            </tbody>
    </table>
    <p><b> {{$settings?->invoice_header_title}}</b></p>
    <p><b>{{$settings?->invoice_header_address}}</b></p>

    <div style="text-align: right; margin-top: 20px;">
        @if($settings?->seal_url)
            <img src="{{ $settings->seal_url }}" alt="Seal" style="width: 150px; height: auto;">
        @endif
    </div>
    <div class="footer">
        <div class="footer-text"  style="font-family: 'Italic Outline Art', sans-serif; font-style: italic;">{{$settings?->invoice_header_title}}</div>
        <span class="page-number">Page: </span>
    </div>
    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
