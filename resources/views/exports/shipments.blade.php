<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sea Cargo Manifest</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
        }
        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }
        .center-text {
            text-align: center;
        }
    </style>
</head>
<body>
<table>
    <thead>
    @php
        $total_nototal = 0;
        $total_vtotal = 0;
        $total_gtotal = 0;
        $row_nototal = 0;
        $row_vtotal = 0;
        $row_gtotal = 0;
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

    <tr>
        <th colspan="10" style="text-align:center;">
            <strong><em>UNIVERSAL FREIGHT SERVICES </em></strong>
        </th>

    </tr>

    <tr>
        <th colspan="10" style = "text-align: center;">
            <strong> <em> SEA CARGO DOOR TO DOOR MANIFEST </em> </strong>
        </th>
    </tr>
    <tr>
        <th colspan="10">
            DATE: <?php echo date('F j, Y'); ?>                                                                                                                                                                SHIPMENT NO:2734
        </th>
    </tr>
    <tr>
        <th colspan="10">
            SHIPPER : UNIVERSAL FREIGHT SERVICES, P.O.BOX: 55239, DOHA, QATAR. TEL: +974 4620961 TEL/FAX: +974 4620812 <br>
            CONSIGNEE: LAKSIRI SEVA (PVT) LTD. NO: 66, NEW NUGE ROAD, PELIYAGODA, SRI LANKA <br>
            NOTIFY : LAKSIRI SEVA (PVT) LTD. NO: 31, ST.ANTHONY'S MAWATHA, COLOMBO - 03, SRI LANKA. TEL: +94 11-2574180/ TEL: +94 11-47722800
        </th>
    </tr>
    <tr>
        <th colspan="10">
            AWB NO 157 0364971                                       TOTAL WEIGHT:KG {{ number_format($total_gtotal, 2) }}                                        TOTAL VOLUME:   {{ number_format($total_vtotal, 2) }}                                   NO OF PKG:- {{ number_format($total_nototal, 0) }}
        </th>
    </tr>
    <tr>
        <th>SR NO</th>
        <th>HBL NO</th>
        <th>NAME OF SHIPPER</th>
        <th>NAME OF CONSIGNEES</th>
        <th>TYPE OF PKGS CARGO TYPE</th>
        <th>NO.OF PKGS</th>
        <th>VOLUME CBM</th>
        <th>GWHT KGS</th>
        <th>DESCRIPTION OF CARGO</th>
        <th>REMARKS</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $item)
        <tr>
            <td >{{ $loop->iteration }}</td>
            <td > {{ $item[0]}} </td>
            <td>{{ $item[1]}} {{ $item[2]}} {{ $item[3]}} {{ $item[4]}}</td>
            <td>{{ $item[5] }} {{ $item[6] }} {{ $item[7] }} {{ $item[8] }} </td>
            <td>
                @foreach ($item[9] as $package)
                    {{ $package['quantity'] }}-{{ $package['package_type'] }}<br>
                @endforeach
            </td>
            <td>
                @foreach ($item[9] as $package)
                    {{ $package['quantity'] }}<br>
                @endforeach
            </td>
            <td>
                @foreach ($item[9] as $package)
                    {{ $package['volume'] }}<br>
                @endforeach
            </td>
            <td>
                @foreach ($item[9] as $package)
                    {{ $package['weight'] }}<br>
                @endforeach
            </td>
            <td>PERSONAL EFFECT</td>
            <td>{{$item[10]}}</td>

        </tr>
    @endforeach
    <tr>
        <td colspan="5"></td>
        <td class="center-text">{{ number_format($total_nototal, 0) }}</td>
        <td class="center-text">{{ number_format($total_vtotal, 3) }}</td>
        <td class="center-text">{{ number_format($total_gtotal, 1) }}</td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="10" style="text-align:left; font-size: 10px; padding-top: 20px;"> UNIVERSAL FREIGHT SERVICES<br>DOHA QATAR <br><span style="font-style:italic; margin-top: 10px;">Universal Freight Services                                                                                                                         Page 1 of 1 </span></td>
    </tr>
    </tbody>
</table>
</body>
</html>
