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
    $itemsPerPageRest = 5;

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
                <th colspan="2" style="border-right: none;">
                    <strong>  OBL  {{$container?->bl_number}}</strong>
                </th>
                <th colspan="4" style="border-left: none; border-right: none !important; text-align: center">UNIVERSAL FREIGHT SERVICES</th>
                <th colspan="5" style="border-left: none; text-align: right">SHIPMENT NO {{$container?->reference}}</th>
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
                    <strong style="font-size: 12px">  {{$container?->vessel_name}}</strong>
                </th>
                <th colspan="4">
                    <strong style="font-size: 12px">DATE LOADED:   {{ \Carbon\Carbon::parse($container?->loading_started_at)->format('Y-m-d') }} <br></strong>
                </th>
                <th colspan="1">
                    <strong style="font-size: 12px">VOYAGE: <br></strong>
                </th>
                <th colspan="1">
                    <strong style="font-size: 12px">  {{$container?->voyage_number}}   <br></strong>
                </th>
                <th colspan="1" >
                    <strong style="font-size: 12px">ETA:   <br></strong>
                </th>
                <th colspan="1">
                    <strong style="font-size: 12px">  {{$container?->estimated_time_of_arrival}}    <br></strong>
                </th>
            </tr>

            <tr>
                <th colspan="11" style="font-size: 12px; font-family: 'Times New Roman',fantasy; ">
                    SHIPPER         : {{$settings?->invoice_header_title}}, {{$settings?->invoice_header_address}}.
                    TEL: {{$settings?->invoice_header_telephone}} <br>
                    CONSIGNEE  : LAKSIRI SEVA (PVT) LTD. NO: 66, NEW NUGE ROAD, PELIYAGODA, SRI LANKA <br>
                    NOTIFY           : LAKSIRI SEVA (PVT) LTD. NO: 31, ST.ANTHONY'S MAWATHA, COLOMBO - 03, SRI LANKA. TEL: +94
                    11-47722800
                </th>
            </tr>

            <tr>
                <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 12px;">
                    <strong>CONTR NO        {{$container?->container_number}}  </strong>
                </th>
                <th colspan="4" style="text-align: center;font-family: 'Times New Roman',fantasy; font-size: 12px;">
                    <strong> SEAL NO:    {{$container?->seal_number}} </strong>
                </th>
                <th colspan="4" style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 12px;">
                    <strong>CONTAINER TYPE: {{$container?->container_type}} </strong>
                </th>
            </tr>
            <tr>
                <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 12px; border-bottom: none">
                    <strong> NO OF PKG   {{ number_format($total_nototal, 0) }} </strong>
                </th>
                <th colspan="4" style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 12px; border-bottom: none">
                    <strong> TOTAL VOLUME  {{ number_format($total_vtotal, 2) }} </strong>
                </th>
                <th colspan="4" style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 12px; border-bottom: none">
                    <strong> TOTAL WEIGHT:KG                 {{ number_format($total_gtotal, 2) }} </strong>
                </th>
            </tr>
            </thead>
        @endif
    </table>
    <table>
        <thead style="padding: 0; margin: 0">
        <tr style="font-family: 'Times New Roman',fantasy; font-size: 14px; background-color: #D8D8D8  ;">
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">SR <br>NO</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">HBL NO</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;"> NAME OF SHIPPER</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">NAME OF CONSIGNEES</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">TYPE OF PKGS</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">NO.OF PKGS</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">VOLUME CBM</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">GWHT</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">DESCRIPTION OF CARGO</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 10px;">DELIVERY</th>
            <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 11px;">REMARKS</th>- New Total Column -->
        </tr>
        </thead>
        <tbody>
        @foreach ($chunk as $index => $item)
            @php
                $totalQuantity = collect($item[9])->sum('quantity');
                $totalVolume = collect($item[9])->sum('volume');
                $totalWeight = collect($item[9])->sum('weight');
                $packageCount = $item[9]->count() ? $item[9]->count() :  0;

                $hblweight = number_format((($total_gtotal / $total_vtotal) * $totalVolume), 2);
            @endphp
            <tr>
                <td rowspan="{{ $packageCount > 4 ? $packageCount + 1 : 5 }}" style="font-size: 12px;">{{ $serialNumber++ }}</td>
                <td rowspan="{{$packageCount > 4 ? $packageCount : 4}}" style="font-size: 12px; vertical-align: top"> {{ $item[0]}} </td>
                <td rowspan="1" style="border-bottom: 0; font-size: 12px; border-left:none;vertical-align: top">{{ $item[1]}}</td>
                <td rowspan="1" style="border-bottom: 0; font-size: 12px; vertical-align: top; border-bottom: 0">{{ $item[5] }} </td>
                <td rowspan="1" style="font-size: 12px; vertical-align: top; border-bottom: 0">{{ $item[9][0]['package_type'] }}</td>
                <td rowspan="1" style="font-size: 12px; vertical-align: top; border-bottom: 0; text-align: center">{{ $item[9][0]['quantity'] }}</td>
                <td rowspan="1" style="font-size: 12px; vertical-align: top; border-bottom: 0; text-align: center">{{ $item[9][0]['volume'] }}</td>
                <td rowspan="1" style="font-size: 12px; vertical-align: top; border-bottom: 0; text-align: center">{{ $item[9][0]['weight'] }}</td>
                <td rowspan="{{ $packageCount > 4 ? $packageCount + 1 : 5 }}" style="font-size: 12px; text-align: center">PERSONAL<br> EFFECT</td>
                <td rowspan="{{ $packageCount > 4 ? $packageCount + 1 : 5 }}" style="font-size: 12px; text-align: center">
                    {{ $item[13] }}
                </td>
                <td rowspan="1" style="font-size: 12px; text-align: center; border-bottom: 0">
                    <b >{{ $item[17] ? $item[17] : '' }}</b>
                </td>
            </tr>

            <tr>
                <td style="font-size: 12px; border-left:none;vertical-align: top; border-top: 0; border-bottom: 0">{{ $item[2] }}</td>
                <td rowspan="2" style="font-size: 12px; border-left:none;vertical-align: top; border-top: 0; border-bottom: 0">{{ $item[6] }}</td>
                <td style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0">{{ isset($item[9][1]) ? $item[9][1]['package_type'] : ' ' }}</td>
                <td style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center">{{ isset($item[9][1]) ? $item[9][1]['quantity'] : ' ' }}</td>
                <td style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center">{{ isset($item[9][1]) ? $item[9][1]['volume'] : ' '}}</td>
                <td style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center">{{ isset($item[9][1]) ? $item[9][1]['weight'] : ' ' }}</td>
                <td rowspan="1" style="font-size: 12px; text-align: center; border-top: 0; vertical-align: top; border-bottom: 0">
                    <b>{{ $item[18] ? $item[18] : '' }}</b>
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; border-left:none;vertical-align: top; border-top: 0; border-bottom: 0">P.O.BOX: {{ $item[14] }}</td>
                <td style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0">{{ isset($item[9][2]) ? $item[9][2]['package_type'] : ' ' }}</td>
                <td style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center">{{ isset($item[9][2]) ? $item[9][2]['quantity'] : ' ' }}</td>
                <td style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center">{{ isset($item[9][2]) ? $item[9][2]['volume'] : ' '}}</td>
                <td style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center">{{ isset($item[9][2]) ? $item[9][2]['weight'] : ' ' }}</td>
                <td rowspan="1" style="font-size: 12px; text-align: center; border-top: 0; vertical-align: top; border-bottom: 0">
                    <b >{{ $item[11] == 'GIFT' ||  $item[11] == 'Gift'? 'GIFT CARGO' : '' }}</b>
                </td>
            </tr>
            <tr>
                <td rowspan="{{ $packageCount > 4 ? $packageCount - 3 : 1 }}" style="font-size: 12px; border-left:none;vertical-align: top; border-top: 0">{{ $item[4] }}</td>
                <td rowspan="{{ $packageCount > 4 ? $packageCount - 3 : 1 }}" style="font-size: 12px; border-left:none;vertical-align: top; border-top: 0; border-bottom: 0">{{ $item[7] }}</td>
                <td rowspan="1" style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0">{{ isset($item[9][3]) ? $item[9][3]['package_type'] : ' ' }}</td>
                <td rowspan="1" style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center">{{ isset($item[9][3]) ? $item[9][3]['quantity'] : ' ' }}</td>
                <td rowspan="1" style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center">{{ isset($item[9][3]) ? $item[9][3]['volume'] : ' '}}</td>
                <td rowspan="1" style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center">{{ isset($item[9][3]) ? $item[9][3]['weight'] : ' ' }}</td>
                <td rowspan="{{ $packageCount > 4 ? $packageCount-2 : 2 }}" style="font-size: 12px; text-align: center; border-top: 0; vertical-align: top">
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
                        <b>NOT PAID <br>PLEASE COLLECT <br>QAR AMOUNT/-</b>
                    @endif
                </td>
            </tr>

            @if(count($item[9])  > 4)
                @php
                    $restPackages = $item[9]->slice(4);
                @endphp
                @foreach($restPackages as $restPkg)
                    <tr>
                        <td rowspan="1" style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0">{{ $restPkg['package_type'] }}</td>
                        <td rowspan="1" style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center">{{ $restPkg['quantity'] }}</td>
                        <td rowspan="1" style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center">{{ $restPkg['volume'] }}</td>
                        <td rowspan="1" style="font-size: 12px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center">{{ $restPkg['weight'] }}</td>
                    </tr>
                @endforeach
            @endif


            <tr>
                <td style="font-size: 12px; border-left:none; vertical-align: middle; border-top: 0">PP No</td>
                <td style="font-size: 12px; border-left:none;vertical-align: middle; border-top: 0">{{ $item[3] }}</td>
                <td style="font-size: 12px; border-left:none; vertical-align: middle; border-top: 0"></td>
                <td style="height: 2px !important; font-size: 12px; border: 2px solid;"><b>TOTAL</b></td>
                <td style="height: 2px !important; text-align: center; font-size: 12px; border: 2px solid; "><b>  <strong> {{ $totalQuantity }}</strong></b></td>
                <td style="height: 2px !important; text-align: right; font-size: 12px; border: 2px solid; "><b> <strong>{{ number_format($totalVolume,2) }}</strong></b></td>
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
                <img src="{{ $settings->seal_url }}" alt="Seal" style="width: 150px; height: auto; opacity: 0.5;">
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
