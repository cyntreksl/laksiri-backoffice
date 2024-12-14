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
            flex-direction: column;  /* Stack elements vertically */
        }


        .name {
            display: flex;
            flex-direction: column; /* Stack elements vertically */
            margin-left: 70px; /* Adjust as needed */
            margin-top: 5px;
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
            <strong><em>
                    <mark style="background-color: #89b3e3">UNIVERSAL FREIGHT SERVICES</mark>
                </em></strong>
        </th>

    </tr>

    <tr>
        <th colspan="10" style="background-color: gray ; text-align: center; ">
            <strong> <em> AIR CARGO MANIFEST </em> </strong>
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
            SHIPPER :       UNIVERSAL FREIGHT SERVICES, P.O.BOX: 55239, DOHA, QATAR. TEL: +974 4620961 TEL/FAX: +974
            4620812 <br>
            CONSIGNEE:  LAKSIRI SEVA (PVT) LTD. NO: 66, NEW NUGE ROAD, PELIYAGODA, SRI LANKA <br>
            NOTIFY :  LAKSIRI SEVA (PVT) LTD. NO: 31, ST.ANTHONY'S MAWATHA, COLOMBO - 03, SRI LANKA. TEL: +94
            11-47722800
        </th>
    </tr>
    <tr>
        <th colspan="3">
            AWB NO 157 0364971
            <hr style="border: 0.3px solid black; margin: 8px 0;">
            NO OF PKG:- {{ number_format($total_nototal, 0) }}
        </th>

        <th colspan="1">
            <p> TOTAL VOLUME: </p>
        </th>
        <th colspan="3">
                               {{ number_format($total_vtotal, 2) }}
        </th>
        <th colspan="1">
            TOTAL WEIGHT
        </th>
        <th colspan="2">
                            {{ number_format($total_gtotal, 2) }}
        </th>
    </tr>

    <tr style="font-family: 'Times New Roman',fantasy; font-size: 14px; background-color: gray ;">
        <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">SR NO</th>
        <th>HBL NO</th>
        <th>  NAME OF SHIPPER </th>
        <th>NAME OF CONSIGNEES</th>
        <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">TYPE OF PKGS CARGO TYPE</th>
        <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">NO.OF PKGS</th>
        <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">VOLUME CBM</th>
        <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">GWHT KGS</th>
        <th style="font-family: 'Times New Roman',fantasy; font-size: 10px;">DESCRIPTION OF CARGO</th>
        <th style="font-family: 'Times New Roman',fantasy; font-size: 11px;">REMARKS</th>
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
            <td>  PERSONAL EFFECT</td>
            <td>     {{$item[10]}}</td>

        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
