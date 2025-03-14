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
            padding: 2px;
            text-align: left;
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
    $itemsPerPageFirst = 4;
    $itemsPerPageRest = 6;

    $firstChunk = array_slice($data, 0, $itemsPerPageFirst);

    $remainingChunks = array_chunk(array_slice($data, $itemsPerPageFirst), $itemsPerPageRest);

    $chunks = array_merge([$firstChunk], $remainingChunks);
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
                <th colspan="3" style="border-right: none;">
                    <strong>OBL  {{$container?->bl_number}}</strong>
                </th>
                <th colspan="4" style="border-left: none; border-right: none !important; text-align: center">UNIVERSAL FREIGHT SERVICES</th>
                <th colspan="4" style="border-left: none; text-align: right">SHIPMENT N0 {{$container?->reference}}</th>
            </tr>
            <tr>
                <th colspan="11" style="background-color: #D8D8D8 ; text-align: center; ">
                    <strong> <em> SEA CARGO MANIFEST </em> </strong>
                </th>
            </tr>
            <tr>
                <th>
                    <strong style="font-size: 12px;">VESSEL:</strong>
                </th>
                <th colspan="2">
                    <strong style="font-size: 12px">{{$container?->vessel_name}}</strong>
                </th>
                <th colspan="4">
                    <strong style="font-size: 12px">DATE LOADED:   {{$container?->loading_started_at}} <br></strong>
                </th>
                <th colspan="1">
                    <strong style="font-size: 12px">VOYAGE: <br></strong>
                </th>
                <th colspan="1">
                    <strong style="font-size: 12px">{{$container?->voyage_number}}   <br></strong>
                </th>
                <th colspan="1" >
                    <strong style="font-size: 12px">ETA:   <br></strong>
                </th>
                <th colspan="1">
                    <strong style="font-size: 12px">{{$container?->estimated_time_of_arrival}}    <br></strong>
                </th>
            </tr>

            <tr>
                <th colspan="11" style="font-size: 12px; font-family: 'Times New Roman',fantasy; ">
                    SHIPPER : {{$settings?->invoice_header_title}}, {{$settings?->invoice_header_address}}.
                    TEL: {{$settings?->invoice_header_telephone}} <br>
                    CONSIGNEE: LAKSIRI SEVA (PVT) LTD. NO: 66, NEW NUGE ROAD, PELIYAGODA, SRI LANKA <br>
                    NOTIFY : LAKSIRI SEVA (PVT) LTD. NO: 31, ST.ANTHONY'S MAWATHA, COLOMBO - 03, SRI LANKA. TEL: +94
                    11-47722800
                </th>
            </tr>

            <tr>
                <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 12px;">
                    <strong>CONTR NO        {{$container?->container_number}}  </strong>
                </th>
                <th colspan="4" style="font-family: 'Times New Roman',fantasy; font-size: 12px;">
                    <strong> SEAL NO:    {{$container?->seal_number}} </strong>
                </th>
                <th colspan="4" style="font-family: 'Times New Roman',fantasy; font-size: 12px;">
                    <strong>CONTAINER TYPE {{$container?->container_type}} </strong>
                </th>
            </tr>
            <tr>
                <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 12px; border-bottom: none">
                    <strong> NO OF PKG   {{ number_format($total_nototal, 0) }} </strong>
                </th>
                <th colspan="4" style="font-family: 'Times New Roman',fantasy; font-size: 12px; border-bottom: none">
                    <strong> TOTAL VOLUME  {{ number_format($total_vtotal, 2) }} </strong>
                </th>
                <th colspan="4" style="font-family: 'Times New Roman',fantasy; font-size: 12px; border-bottom: none">
                    <strong> TOTAL WEIGHT:KG                 {{ number_format($total_gtotal, 2) }} </strong>
                </th>
            </tr>
            </thead>
            @endif
    </table>
    <table>
        <thead style="padding: 0; margin: 0">
            <tr style="font-family: 'Times New Roman',fantasy; font-size: 14px; background-color: #D8D8D8  ;">
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">SR NO</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">HBL NO</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;"> NAME OF SHIPPER</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">NAME OF CONSIGNEES</th>
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
                    <td rowspan="1" style="font-size: 12px; border-bottom: 0">{{ $serialNumber++ }}</td>
                    <td rowspan="1" style="font-size: 12px; border-right:none ;vertical-align: top"> {{ $item[0]}} </td>
                    <td rowspan="1" style="font-size: 12px; border-left:none;vertical-align: top">{{ $item[1]}} {{ $item[2]}} <br>  {{ $item[3]}} <br> {{ $item[4]}}</td>
                    <td rowspan="1" style="font-size: 12px; vertical-align: top; border-bottom: 0">{{ $item[5] }} <br> {{ $item[6] }} </td>
                    <td colspan="4" style="vertical-align: top; font-size: 13px; padding: 0 !important;">
                        @php
                            $totalQuantity = collect($item[9])->sum('quantity');
                            $totalVolume = number_format(collect($item[9])->sum('volume'),2);
                            $totalWeight = collect($item[9])->sum('weight');

                            $hblweight = number_format(($total_gtotal / $total_vtotal * $totalVolume), 2);
                        @endphp

                        <table style="font-size: 12px; width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">

                            @if(count($item[9]) === 1)
                                <tr>
                                    <td style="padding-left: 3px; text-align: left; width: 30% !important; border-left: none; border-top: none; border-bottom: none;">{{ $package['package_type'] }}</td>
                                    <td style="text-align: center; width: 25% !important; border-top: none; border-bottom: none; padding: 0;">{{ $package['quantity'] }}</td>
                                    <td style="text-align: center; width: 29% !important; border-top: none; border-bottom: none; padding: 0;">{{ $package['volume'] }}</td>
                                    <td style="text-align: center; width: 16% !important; border-top: none; border-right: none; border-bottom: none; padding: 0;">{{ $package['weight'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 4px; text-align: left; width: 30% !important; border-left: none; border-top: none; border-bottom: none;"> </td>
                                    <td style="text-align: center; width: 25% !important; border-top: none; border-bottom: none; padding: 0;"> </td>
                                    <td style="text-align: center; width: 29% !important; border-top: none; border-bottom: none; padding: 0;"> </td>
                                    <td style="text-align: center; width: 16% !important; border-top: none; border-right: none; border-bottom: none; padding: 0;"> </td>
                                </tr>
                            @else
                                @foreach ($item[9] as $package)
                                    <tr>
                                        <td style="font-size: 12px; padding-left: 4px; text-align: left; width: 30% !important; border-left: none; border-top: none; border-bottom: none;">{{ $package['package_type'] }}</td>
                                        <td style="font-size: 12px; text-align: center; width: 25% !important; border-top: none; border-bottom: none; padding: 0;">{{ $package['quantity'] }}</td>
                                        <td style="font-size: 12px; text-align: center; width: 29% !important; border-top: none; border-bottom: none; padding: 0;">{{ $package['volume'] }}</td>
                                        <td style="font-size: 12px; text-align: center; width: 16% !important; border-top: none; border-right: none; border-bottom: none; padding: 0;">{{ $package['weight'] }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </table>
                    </td>
                    <td rowspan="2" style="font-size: 12px">  PERSONAL<br>      EFFECT</td>
                    <td rowspan="2" style="font-size: 12px">
                        {{ $item[13] }}
                    </td>

                    <td rowspan="2" style="font-size: 12px; text-align: center">
                        <b >{{ $item[11] == 'GIFT' ? $item[11] : '' }}
                            @if (!empty($item[12]))
                                <p>{{ $container?->port_of_loading . '&' . $container?->port_of_discharge }}</p>
                            @endif

                            <p>{{ $item[10]}}</p>
                        </b>
                    </td>
                </tr>
                <tr>
                    <td style="border-top: 0;"></td>
                    <td style="font-size: 12px;">PP No</td>
                    <td style="font-size: 12px;">{{ $item[3] }}</td>
                    <td style="font-size: 12px; border-top: 0">{{  $item[8] }}</td>
                    <td style="height: 2px !important; font-size: 12px; border: 2px solid;"><b>TOTAL</b></td>
                    <td style="height: 2px !important; text-align: center; font-size: 12px; border: 2px solid; "><b>  <strong> {{ $totalQuantity }}</strong></b></td>
                    <td style="height: 2px !important; text-align: right; font-size: 12px; border: 2px solid; "><b> <strong>{{ $totalVolume }}</strong></b></td>
                    <td style="height: 2px !important; text-align: right; font-size: 12px; border: 2px solid; "><b>   <strong > {{ $hblweight }}</strong></b></td>
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

    <div class="footer">
        <div style="text-align: right; margin-top: 20px; margin-right: 50px !important;">
            @if($settings?->seal_url)
                <img src="{{ $settings->seal_url }}" alt="Seal" style="width: 150px; height: auto;">
            @endif
        </div>
        <div class="footer-text"  style="font-family: 'Italic Outline Art', sans-serif; font-style: italic;">{{$settings?->invoice_footer_title}}</div>
        <div class="footer-text"  style="font-family: 'Italic Outline Art', sans-serif; font-style: italic;">{{$settings?->invoice_header_address}}</div>
        <span class="page-number">Page: </span>
    </div>
    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
