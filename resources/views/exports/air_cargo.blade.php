<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> AIR Cargo Manifest Free </title>
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
    $itemsPerPage = 5; // Number of rows per page
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
                <th colspan="10" style="text-align:center;">
                    <strong><em>
                            UNIVERSAL FREIGHT SERVICES
                        </em></strong>
                </th>
            </tr>
            <tr>
                <th colspan="10" style="background-color: #D8D8D8 ; text-align: center; ">
                    <strong> <em> AIR CARGO MANIFEST </em> </strong>
                </th>
            </tr>
            <tr>
                <th colspan="2">

                </th>
                <th colspan="1">

                </th>
                <th colspan="4" style="font-family: 'Times New Roman',fantasy; font-size: 12px; border-right: 0">
                    DATE: <?php echo date('d/m/Y'); ?>
                    <span style="font-family: 'Times New Roman',fantasy; font-size: 12px; text-align: right"> SHIPMENT NO.: {{$container?->reference}} </span>
                </th>
                <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 12px; text-align: right; border-left: 0">
                    SHIPMENT NO.: {{$container?->reference}}
                </th>
            </tr>

            <tr>
                <th colspan="10" style="font-family: 'Times New Roman',fantasy; font-size: 12px;">
                    SHIPPER         : {{$settings?->invoice_header_title}}, {{$settings?->invoice_header_address}}.
                    TEL: {{$settings?->invoice_header_telephone}} <br>
                    CONSIGNEE : LAKSIRI SEVA (PVT) LTD. NO: 66, NEW NUGE ROAD, PELIYAGODA, SRI LANKA <br>
                    NOTIFY           : LAKSIRI SEVA (PVT) LTD. NO: 31, ST.ANTHONY'S MAWATHA, COLOMBO - 03, SRI LANKA. TEL: +94
                    11-47722800
                </th>
            </tr>

            <tr>
                <th colspan="3" style="height: 3px !important; font-size: 12px; text-align: center">AWB NO {{$container?->awb_number}}</th>
                <th rowspan="2" colspan="1" style="font-size: 12px; text-align: center"><p> TOTAL VOLUME: </p></th>
                <th rowspan="2" colspan="3" style="font-size: 12px; text-align: center"> {{ number_format($total_vtotal, 2) }}</th>
                <th rowspan="2" colspan="1" style="font-size: 12px; text-align: center">TOTAL WEIGHT</th>
                <th rowspan="2" colspan="2" style="font-size: 12px; text-align: center"> {{ number_format($total_gtotal, 2) }}</th>

            </tr>
            <tr>
                <th colspan="3" style="font-size: 12px;"> NO OF PKG: {{ number_format($total_nototal, 0) }}</th>
            </tr>
            @endif
            <tr style="font-family: 'Times New Roman',fantasy; font-size: 12px; background-color: #D8D8D8  ;">
                <th style="font-family: 'Times New Roman',fantasy; font-size: 12px;">SR NO</th>
                <th>HBL NO</th>
                <th> NAME OF SHIPPER</th>
                <th>NAME OF CONSIGNEES</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 12px;">TYPE OF PKGS</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 12px;">NO.OF PKGS</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 12px;">VOLUME CBM</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 12px;">GWHT</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 12px;">DESCRIPTION OF CARGO</th>
                <th rowspan="1" style="font-family: 'Times New Roman',fantasy; font-size: 12px;">REMARKS</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($chunk as $index => $item)
                <tr style="font-size: 12px;">
                    <td>{{ $loop->iteration }}</td>
                    <td style="border-right:none ;vertical-align: top"> {{ $item[0]}} </td>
                    <td style="border-left:none;vertical-align: top">{{ $item[1]}} {{ $item[2]}} <br>  {{ $item[3]}} <br> {{ $item[4]}}</td>
                    <td style="vertical-align: top">{{ $item[5] }} <br> {{ $item[6] }} <br> {{ $item[7] }} <br> {{ $item[8] }} </td>
                    <td style="vertical-align: top">
                        @foreach ($item[9] as $package)
                            {{ $package['quantity'] }}-{{ $package['package_type'] }}<br>
                        @endforeach
                    </td>
                    <td style="vertical-align: top; text-align: center">
                        {{ $item[9]->sum('quantity') }}
                    </td>
                    <td style="vertical-align: top; text-align: center">
                        {{ $item[9]->sum('volume') }}
                    </td>
                    <td style="vertical-align: top; text-align: center">
                        {{ number_format((($total_gtotal/$total_vtotal)*$item[9]->sum('volume')),2) }}
                    </td>
                    <td>  PERSONAL<br>      EFFECT</td>
                    <td style="text-align: center; vertical-align: auto">
                        <b >{{ $item[11] == 'GIFT' ||  $item[11] == 'Gift'? 'GIFT CARGO' : '' }}</b>
                        <br>
                        @if($item[15] && $item[16])
                            <b>
                                DOHA & {{ $item[13] }}
                                <br>
                                PAID
                            </b>
                        @elseif($item[15])
                            <b>
                                PAID
                            </b>
                        @else
                            <b>PLEASE COLLECT QAR AMOUNT/-</b>
                        @endif
                    </td>

                </tr>
            @endforeach
            @if ($loop->last)
                <tr style="border: none; font-size: 12px;">
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
