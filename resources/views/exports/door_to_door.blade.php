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
            flex-direction: column;  /* Stack elements vertically */
        }

        .name {
            display: flex;
            flex-direction: column; /* Stack elements vertically */
            margin-left: 70px; /* Adjust as needed */
            margin-top: 5px;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        .footer-text {
            text-align: left;
        }
        .page-number {
            text-align: right;
        }
        .page-number:after {
            content: counter(page);
        }

    </style>
</head>
<body >

@foreach($groupedData as $groupIndex => $data)
@php
    $itemsPerPage = 6; // Number of rows per page - Reduced to fit the content better
    $chunks = array_chunk($data, $itemsPerPage); // Split data into chunks of $itemsPerPage
    $total_nototal = 0;
    $total_vtotal = 0;
    $total_gtotal = 0;
    $mhbl = [];
@endphp
@foreach($data as $item)
    @php
        $mhbl = $item[10];
    @endphp
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
                <th colspan="10" style="background-color: 	#C8C8C8 ; text-align: center; ">
                    <strong> <em> SEA CARGO DOOR TO DOOR MANIFEST </em> </strong>
                </th>
            </tr>
            <tr>
                <th colspan="2">

                </th>
                <th colspan="1">

                </th>
                <th colspan="7">DATE: <?php echo date('d/m/Y'); ?>                                                      
                           
                                                    SHIPMENT NO :602

                </th>
            </tr>

            <tr>
                <th colspan="10" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
                    SHIPPER : {{$settings?->invoice_header_title}}, {{$settings?->invoice_header_address}}.
                    +974
                    4620812 <br>
                    CONSIGNEE:  LAKSIRI SEVA (PVT) LTD. NO: 66, NEW NUGE ROAD, PELIYAGODA, SRI LANKA <br>
                    NOTIFY :  LAKSIRI SEVA (PVT) LTD. NO: 31, ST.ANTHONY'S MAWATHA, COLOMBO - 03, SRI LANKA. TEL: +94
                    11-47722800
                </th>
            </tr>

            <tr>
                <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
                    <strong>CONTR NO       {{$item[12] }} </strong>
                </th>
                <th colspan="4" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
                    <strong> MHBL </strong> {{$mhbl->hbl_number ?? $mhbl->reference}}
                </th>
                <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
                    <strong>    </strong>
                </th>
            </tr>
            <tr>
                <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
                    <strong> NO OF PKG   {{ number_format($total_nototal, 0) }} </strong>
                </th>
                <th colspan="4" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
                    <strong> TOTAL VOLUME  {{ number_format($total_vtotal, 2) }} </strong>
                </th>
                <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">
                    <strong> TOTAL WEIGHT:KG                 {{ number_format($total_gtotal, 2) }} </strong>
                </th>
            </tr>


            <tr style="font-family: 'Times New Roman',fantasy; font-size: 14px; background-color: #C8C8C8 ;">
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">SR NO</th>
                <th>HBL NO</th>
                <th> NAME OF SHIPPER</th>
                <th>NAME OF CONSIGNEES</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">TYPE OF PKGS CARGO TYPE</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">NO.OF PKGS</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">VOLUME CBM</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">GWHT KGS</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">DESCRIPTION OF CARGO</th>
                <th style="font-family: 'Times New Roman',fantasy; font-size: 11px;">REMARKS</th>

            </tr>
            </thead>
        @endif
        <tbody>
        @foreach($chunk as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td style="border-right:none; vertical-align: top"> {{ $item[0]}} </td>
                <td style="vertical-align: top">{{ $item[1]}} {{ $item[2]}}  <br>  {{ $item[3]}} <br> {{ $item[4]}}</td>
                <td>{{ $item[5] }} <br> {{ $item[6] }} <br> {{ $item[7] }} <br> {{ $item[8] }} </td>
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
                <td><b>     {{$item[11]}}</b></td>

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
        @if ($chunkIndex === count($chunks) - 1)

        @endif
    </table>
    <div style="text-align: right; margin-top: 20px;">
        @if($settings?->seal_url)
            <img src="{{ $settings->seal_url }}" alt="Seal" style="width: 150px; height: auto;">
        @endif
    </div>
    <div class="footer">
        <div class="footer-text"
             style="font-family: 'Italic Outline Art', sans-serif; font-style: italic;">{{$settings?->invoice_header_title}}</div>
        <div class="page-number"><i>Page <span class="page-number"></span> of {{count($chunks)}}</i></div>
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
