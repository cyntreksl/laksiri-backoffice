<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> SEA CARGO DOOR TO DOOR MANIFEST </title>
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
<body >

@foreach($groupedData as $groupIndex => $data)
    @php
        $isNoMHBL = $groupIndex === 'no_mhbl';
    @endphp

    {{-- Now render the chunking and table as before --}}
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
        $mhbl = [];
    @endphp
    @foreach($data as $item)
        @php
            $mhbl = $item[20];
        @endphp
        @foreach ($item[9] as $package)
            @php
                $total_nototal += $package['quantity'];
                $total_vtotal += $package['volume'];
                $total_gtotal += $package['actual_weight'];
            @endphp
        @endforeach
    @endforeach

    @foreach ($chunks as $chunkIndex => $chunk)
        <table class="hbl-content">
            <thead style="padding: 0; margin: 0">
            @if ($chunkIndex === 0)
                <tr>
                    <th colspan="10" style="text-align: center; height: 3% !important;">UNIVERSAL FREIGHT SERVICES</th>
                </tr>
                <tr>
                    <th colspan="10" style="background-color: #D8D8D8 ; text-align: center; height: 3% !important;">
                        <strong> <em> {{ $container->cargo_type === 'Sea Cargo' ? 'SEA CARGO' : 'AIR CARGO' }} DOOR TO DOOR MANIFEST  </em> </strong>
                    </th>
                </tr>
                <tr>
                    <th colspan="2" style="height: 3% !important;">
                        <strong style="font-size: 11px;"></strong>
                    </th>
                    <th colspan="1" style="height: 3% !important;">
                        <strong style="font-size: 11px"></strong>
                    </th>
                    <th colspan="5" style="height: 3% !important; border-right: 0 !important;">
                        <strong style="font-size: 11px">DATE LOADED:   {{ \Carbon\Carbon::parse($container?->loading_started_at)->format('Y-m-d') }} <br></strong>
                    </th>
                    <th colspan="2" style="text-align: left; height: 3% !important; border-left: 0 !important;">
                        <strong>SHIPMENT NO {{$container?->reference}}</strong>
                    </th>
                </tr>
                <tr>
                    <th colspan="10" style="font-size: 11px; font-family: 'Times New Roman',fantasy; border-bottom: 0">
                        SHIPPER         : {{$settings?->invoice_header_title}}, {{$settings?->invoice_header_address}}.
                        TEL: {{$settings?->invoice_header_telephone}}
                    </th>
                </tr>
                <tr>
                    <th colspan="10" style="font-size: 11px; font-family: 'Times New Roman',fantasy; border-bottom: 0; border-top: 0">
                        CONSIGNEE  : LAKSIRI SEVA (PVT) LTD. NO: 66, NEW NUGE ROAD, PELIYAGODA, SRI LANKA
                    </th>
                </tr>
                <tr>
                </tr>
                <th colspan="10" style="font-size: 11px; font-family: 'Times New Roman',fantasy; border-top: 0">
                    NOTIFY           : LAKSIRI SEVA (PVT) LTD. NO: 31, ST.ANTHONY'S MAWATHA, COLOMBO - 03, SRI LANKA. TEL: +94
                    11-47722800
                </th>
                <tr>
                    <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 11px;">
                        <strong>{{$container?->cargo_type === 'Sea Cargo' ? 'CONTR NO '. $container?->container_number : 'AWB NO '.$container?->awb_number}}</strong>
                    </th>

                    <th colspan="4" style="text-align: left;font-family: 'Times New Roman',fantasy; font-size: 11px; border-bottom: none">
                        @if($mhbl)
                            <strong> MHBL {{ $mhbl->hbl_number }}</strong>
                        @endif
                    </th>
                    <th colspan="3" style="text-align: left;font-family: 'Times New Roman',fantasy; font-size: 11px;">
                    </th>
                </tr>
                <tr>
                    <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 11px; border-bottom: none">
                        <strong> NO OF PKG   {{ number_format($total_nototal, 0) }} </strong>
                    </th>
                    <th colspan="4" style="text-align: left;font-family: 'Times New Roman',fantasy; font-size: 11px; border-bottom: none">
                        <strong> TOTAL VOLUME {{ number_format($total_vtotal, 3) }}</strong>
                    </th>
                    <th colspan="3" style="text-align: left;font-family: 'Times New Roman',fantasy; font-size: 11px;">
                        <strong> TOTAL WEIGHT:KG {{ number_format($total_gtotal, 2) }}</strong>
                    </th>
                </tr>
            @endif
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
                <th style="text-align: center; font-family: 'Times New Roman',fantasy; font-size: 11px;">REMARKS</th>- New Total Column -->
            </tr>
            </thead>
            <tbody>
            @foreach ($chunk as $index => $item)
                @php
                    $totalQuantity = collect($item[9])->sum('quantity');
                    $totalVolume = collect($item[9])->sum('volume');
                    $totalWeight = collect($item[9])->sum('actual_weight');
                    $packageCount = $item[9]->count() ? $item[9]->count() :  0;

                    $hblweight = number_format((($total_gtotal / $total_vtotal) * $totalVolume), 2);
                @endphp
                <tr>
                    <td rowspan="{{ $packageCount > 5 ? $packageCount : 5 }}" style="font-size: 11px;">{{ $serialNumber++ }}</td>
                    <td rowspan="{{ $packageCount > 5 ? $packageCount : 5 }}" style="font-size: 11px; vertical-align: top"> {{ $item[0]}} </td>
                    <td rowspan="1" style="border-bottom: 0; font-size: 11px; border-left:none;vertical-align: top">{{ $item[1]}}</td>
                    <td rowspan="1" style="border-bottom: 0; font-size: 11px; vertical-align: top; border-bottom: 0">{{ $item[5] }} </td>
                    <td rowspan="1" style="font-size: 11px; vertical-align: top; border-bottom: 0">{{ $item[9][0]['quantity'] }}-{{ $item[9][0]['package_type'] }}</td>
                    <td rowspan="1" style="font-size: 11px; vertical-align: top; border-bottom: 0; text-align: center">{{ $packageCount }}</td>
                    <td rowspan="1" style="font-size: 11px; vertical-align: top; border-bottom: 0; text-align: center">{{$totalVolume}}</td>
                    <td rowspan="1" style="font-size: 11px; vertical-align: top; border-bottom: 0; text-align: center">{{ $totalWeight }}</td>
                    <td rowspan="{{$packageCount > 5 ? $packageCount : 5}}" style="font-size: 11px; text-align: center">PERSONAL<br> EFFECT</td>
                    <td rowspan="1" style="font-size: 11px; text-align: center; border-bottom: 0">
                        <b >{{ $item[17] ? $item[17] : '' }}</b>
                    </td>
                </tr>

                <tr>
                    <td style="font-size: 11px; border-left:none;vertical-align: top; border-top: 0; border-bottom: 0">{{ $item[2] }}</td>
                    <td rowspan="2" style="font-size: 11px; border-left:none;vertical-align: top; border-top: 0; border-bottom: 0">{{ $item[6] }}</td>
                    <td style="font-size: 11px; vertical-align: top; border-top: 0; border-bottom: 0">{{ isset($item[9][1]) ? $item[9][1]['quantity'] . ' - ' . $item[9][1]['package_type'] : ' ' }}</td>
                    <td style="font-size: 11px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center"></td>
                    <td style="font-size: 11px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center"></td>
                    <td style="font-size: 11px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center"></td>
                    <td rowspan="1" style="font-size: 11px; text-align: center; border-top: 0; vertical-align: top; border-bottom: 0">
                        <b>{{ $item[18] ? $item[18] : '' }}</b>
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 11px; border-left:none;vertical-align: top; border-top: 0; border-bottom: 0">{{ $item[14] }}</td>
                    <td style="font-size: 11px; vertical-align: top; border-top: 0; border-bottom: 0">{{ isset($item[9][2]) ? $item[9][2]['quantity'] . ' - ' . $item[9][2]['package_type'] : ' ' }}</td>
                    <td style="font-size: 11px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center"></td>
                    <td style="font-size: 11px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center"></td>
                    <td style="font-size: 11px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center"></td>
                    <td rowspan="1" style="font-size: 11px; text-align: center; border-top: 0; vertical-align: top; border-bottom: 0">
                        <b >{{ $item[11] == 'GIFT' ||  $item[11] == 'Gift'? 'GIFT CARGO' : '' }}</b>
                    </td>
                </tr>
                <tr>
                    <td rowspan="1" style="font-size: 11px; border-left:none;vertical-align: top; border-top: 0; border-bottom: 0">{{ $item[3] }}</td>
                    <td rowspan="1" style="font-size: 11px; border-left:none;vertical-align: top; border-top: 0; border-bottom: 0">{{ $item[7] }}</td>
                    <td rowspan="1" style="font-size: 11px; vertical-align: top; border-top: 0; border-bottom: 0">{{ isset($item[9][3]) ? $item[9][3]['quantity'] . ' - ' . $item[9][3]['package_type'] : ' ' }}</td>
                    <td rowspan="1" style="font-size: 11px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center"></td>
                    <td rowspan="1" style="font-size: 11px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center"></td>
                    <td rowspan="1" style="font-size: 11px; vertical-align: top; border-top: 0; border-bottom: 0; text-align: center"></td>
                    <td rowspan="{{ $packageCount > 5 ? $packageCount-3 : 2 }}" style="font-size: 11px; text-align: center; border-top: 0; vertical-align: top">
                        @if($item[15] && $item[16])
                            <b>
                                {{ $branch['branchCode'] }} & {{ $item[13] }}
                                <br>
                                PAID
                            </b>
                        @elseif($item[15])
                            <b>
                                PAID
                            </b>
                        @else
                            <b>NOT PAID <br>PLEASE COLLECT <br>{{ $item[19] }}/-</b>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td rowspan="{{ $packageCount > 5 ? $packageCount - 4 : 1 }}" style="font-size: 11px; border-left:none;vertical-align: top; border-top: 0">{{ $item[4] }}</td>
                    <td rowspan="{{ $packageCount > 5 ? $packageCount - 4 : 1 }}" style="font-size: 11px; border-left:none;vertical-align: top; border-top: 0;">{{ $item[8] }}</td>
                    <td
                        rowspan="1"
                        style="font-size: 11px; vertical-align: top; border-top: 0; {{ $packageCount > 5 ? 'border-bottom: 0;' : '' }}">
                        {{ isset($item[9][4]) ? $item[9][4]['quantity'] . ' - ' . $item[9][4]['package_type'] : ' ' }}
                    </td>
                    <td rowspan="1" style="font-size: 11px; vertical-align: top; border-top: 0; {{ $packageCount > 5 ? 'border-bottom: 0;' : '' }}"></td>
                    <td rowspan="1" style="font-size: 11px; vertical-align: top; border-top: 0; {{ $packageCount > 5 ? 'border-bottom: 0;' : '' }}"></td>
                    <td rowspan="1" style="font-size: 11px; vertical-align: top; border-top: 0; {{ $packageCount > 5 ? 'border-bottom: 0;' : '' }}"></td>
                </tr>

                @if(count($item[9])  > 5)
                    @php
                        $restPackages = $item[9]->slice(5);
                    @endphp
                    @foreach($restPackages as $restPkg)
                        <tr>
                            <td
                                rowspan="1"
                                style="font-size: 11px; vertical-align: top; border-top: 0; {{ $loop->last ? 'border-bottom: 1px solid #000;' : 'border-bottom: 0;' }}">
                                {{ $restPkg['quantity'] }} - {{ $restPkg['package_type'] }}
                            </td>
                            <td rowspan="1" style="font-size: 11px; vertical-align: top; border-top: 0; {{ $loop->last ? 'border-bottom: 1px solid #000;' : 'border-bottom: 0;' }}; text-align: center"></td>
                            <td rowspan="1" style="font-size: 11px; vertical-align: top; border-top: 0; {{ $loop->last ? 'border-bottom: 1px solid #000;' : 'border-bottom: 0;' }}; text-align: center"></td>
                            <td rowspan="1" style="font-size: 11px; vertical-align: top; border-top: 0; {{ $loop->last ? 'border-bottom: 1px solid #000;' : 'border-bottom: 0;' }}; text-align: center"></td>
                        </tr>
                    @endforeach
                @endif

            @endforeach
            @if ($loop->last)
                <tr style="border: none; line-height: 20px !important; font-size: 12px;">
                    <td colspan="3" style="border-bottom: 0; border-left: 0; border-right: 0; text-align: center;"></td>
                    <td colspan="2" style="border-bottom: 0; border-left: 0; border-left: 0; border-right: 0; text-align: left;"><strong>GRAND TOTAL</strong></td>
                    <td style="border: none; text-align: center;"><strong><u>{{ number_format($total_nototal, 0) }}</u></strong></td>
                    <td style="border: none; text-align: center;"><strong><u>{{ number_format($total_vtotal, 2) }}</u></strong></td>
                    <td style="border: none; text-align: center;"><strong><u> {{ number_format($total_gtotal, 2) }}</u></strong></td>
                    <td style="border: none;">&nbsp;</td>
                    <td style="border: none;">&nbsp;</td>
                </tr>
                <tr style="border: none; line-height: 20px !important; font-size: 12px;">
                    <td colspan="2" style="border: none; text-align: center;"></td>
                    <td colspan="8" style="border: none; text-align: left;">
                        <strong>
                            UNIVERSAL FREIGHT SERVICES
                            <br>
                                     DOHA QATAR
                        </strong>
                    </td>
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
        @if ($chunkIndex < count($chunks) - 1)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
    @if (!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif
@endforeach

</body>
</html>
