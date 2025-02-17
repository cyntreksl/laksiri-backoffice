<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> SEA Cargo Manifest Free </title>
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
                <th colspan="11" >
                    <strong>
                   OBL  {{$container?->bl_number}}                                                   UNIVERSAL FREIGHT SERVICES
                                                  REFERENCE NUMBER {{$container?->reference}}
                    </strong>
                </th>
            </tr>
            <tr>
                <th colspan="11" style="background-color: #D8D8D8 ; text-align: center; ">
                    <strong> <em> SEA CARGO MANIFEST </em> </strong>
                </th>
            </tr>
            <tr>
                <th colspan="2" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">
                    VESSEL: {{$container?->vessel_name}} <br>
                </th>
                <th colspan="1" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">
                    DATE LOADED:   {{$container?->loading_started_at}} <br>
                </th>
                <th colspan="1" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">
                    VOYAGE: <br>
                </th>
                <th colspan="2" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">
                    {{$container?->voyage_number}}   <br>
                </th>
                <th colspan="2" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">
                    ETA:   <br>
                </th>
                <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">
                    {{$container?->estimated_time_of_arrival}}    <br>
                </th>
            </tr>

            <tr>
                <th colspan="11" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
                    SHIPPER : {{$settings?->invoice_header_title}}, {{$settings?->invoice_header_address}}.
                    TEL: {{$settings?->invoice_header_telephone}} <br>
                    CONSIGNEE: LAKSIRI SEVA (PVT) LTD. NO: 66, NEW NUGE ROAD, PELIYAGODA, SRI LANKA <br>
                    NOTIFY : LAKSIRI SEVA (PVT) LTD. NO: 31, ST.ANTHONY'S MAWATHA, COLOMBO - 03, SRI LANKA. TEL: +94
                    11-47722800
                </th>
            </tr>

            <tr>
                <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
                    <strong>CONTR NO        {{$container?->container_number}}  </strong>
                </th>
                <th colspan="4" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
                    <strong> SEAL NO:    {{$container?->seal_number}} </strong>
                </th>
                <th colspan="4" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
                    <strong>CONTAINER TYPE {{$container?->container_type}} </strong>
                </th>
            </tr>
            <tr>
                <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
                    <strong> NO OF PKG   {{ number_format($total_nototal, 0) }} </strong>
                </th>
                <th colspan="4" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
                    <strong> TOTAL VOLUME  {{ number_format($total_vtotal, 2) }} </strong>
                </th>
                <th colspan="4" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
                    <strong> TOTAL WEIGHT:KG                 {{ number_format($total_gtotal, 2) }} </strong>
                </th>
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
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">    </th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 11px;">REMARKS</th>- New Total Column -->
            </tr>
            </thead>
            <tbody>
            @foreach ($chunk as $index => $item)
                <tr>
                    <td rowspan="2">{{ $loop->iteration }}</td>
                    <td rowspan="2" style="border-right:none ;vertical-align: top"> {{ $item[0]}} </td>
                    <td rowspan="2" style="border-left:none;vertical-align: top">{{ $item[1]}} {{ $item[2]}} <br>  {{ $item[3]}} <br> {{ $item[4]}}</td>
                    <td rowspan="2" style="vertical-align: top">{{ $item[5] }} <br> {{ $item[6] }} <br> {{ $item[7] }} <br> {{ $item[8] }} </td>
                    <td style="vertical-align: top; font-size: 13px;" >
                        @php
                            $totalQuantity = collect($item[9])->sum('quantity');
                            $totalVolume = collect($item[9])->sum('volume');
                            $totalWeight = collect($item[9])->sum('weight');

                            $hblweight = $total_gtotal/$total_vtotal*$totalVolume;
                        @endphp

                        @foreach ($item[9] as $package)
                            {{ $package['quantity'] }}-{{ $package['package_type'] }}
                        @endforeach
                        <br style="margin-bottom:5px;"/>
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
                    <td rowspan="2">  PERSONAL<br>      EFFECT</td>
                    <td rowspan="2">
                        {{ $container?->target_warehouse == 2 ? 'CMB' : ($container?->target_warehouse == 3 ? 'NTR' : $container?->target_warehouse) }}
                    </td>

                    <td rowspan="2" style="text-align: center">
                        <b >{{ $item[11] == 'GIFT' ? $item[11] : '' }}
                            @if (!empty($item[12]))
                                <p>{{ $container?->port_of_loading . '&' . $container?->port_of_discharge }}</p>
                            @endif

                            <p>{{ $item[10]}}</p>
                        </b>
                    </td>
                </tr>
                <tr>
                    <td style="border: 2px solid"><b>TOTAL</b></td>
                    <td style="border: 2px solid"><b>  <strong> {{ $totalQuantity }}</strong></b></td>
                    <td style="border: 2px solid"><b> <strong>{{ $totalVolume }}</strong></b></td>
                    <td style="border: 2px solid"><b>   <strong > {{ $hblweight }}</strong></b></td>
                </tr>

            @endforeach
            @if ($loop->last)
                <tr style="border: none;">
                    <td colspan="5" style="border: none; text-align: right;"></td>
                    <td style="border: none; text-align: center;"><strong><u>{{ number_format($total_nototal, 0) }}</u></strong></td>
                    <td style="border: none; text-align: center;"><strong><u>{{ number_format($total_vtotal, 2) }}</u></strong></td>
                    <td style="border: none; text-align: center;"><strong><u> {{ number_format($total_gtotal, 2) }}</u></strong></td>
                    <td style="border: none;">&nbsp;</td>
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
