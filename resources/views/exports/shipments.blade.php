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
        th {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }
        td {
            border: 1px solid black;
            padding: 5px;
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
            DATE:<span id="current-date"></span>                                                                                                                                                                 SHIPMENT NO:2734
        </th>
    </tr>
    <tr>
        <th colspan="10">
          SHIPPER : UNIVERSAL FREIGHT SERVICES, P.O.BOX: 55239, DOHA, QATAR. TEL: +974 4620961 TEL/FAX: +974 4620812 <br>
            CONSIGNEE: LAKSIRI SEVA (PVT) LTD. NO: 66, NEW NUGE ROAD, PELIYAGODA, SRI LANKA <br>
            NOTIFY : LAKSIRI SEVA (PVT) LTD. NO: 31, ST.ANTHONY'S MAWATHA, COLOMBO - 03, SRI LANKA. TEL: +94 11-2574180/ TEL: +94 11-47722800
        </th>
    </tr>
    @php
        $nototal = 0;
        $vtotal = 0;
        $gtotal = 0;
        foreach($data as $item){
            $nototal += floatval($item[4]);
            $vtotal += floatval($item[5]);
            $gtotal += floatval($item[6]);
        }
    @endphp
    <tr>
        <th colspan="10">
            CONTR NO ONEU0364971                                       MHBL -122122                                        TOTAL VOLUME:  {{ number_format($vtotal, 3) }}                                       TOTAL WEIGHT:KG   {{ number_format($gtotal, 1) }}
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
            <td rowspan="4" >{{ $loop->iteration }}</td>
            <td rowspan="4" > {{ $item[0]}} </td>
            <td rowspan="4" >{{ $item[1]}}</td>
            <td rowspan="4">{{ $item[2] }} {{ $item[3] }} {{ $item[4] }} {{ $item[5] }} </td>
            <td>Yes</td>
            <td>Yes</td>
            <td>Yes</td>
            <td>Yes</td>
            <td>Yes</td>
            <td></td>

        </tr>
    @endforeach
    <tr>
        <td colspan="5"></td>
        <td class="center-text">{{ $nototal }}</td>
        <td class="center-text">{{ number_format($vtotal, 3) }}</td>
        <td class="center-text">{{ number_format($gtotal, 1) }}</td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="10" style="text-align:left; font-size: 10px; padding-top: 20px;"> UNIVERSAL FREIGHT SERVICES<br>DOHA QATAR <br><span style="font-style:italic; margin-top: 10px;">Universal Freight Services                                                                                                                         Page 1 of 1 </span></td>
    </tr>
    </tbody>
</table>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date').textContent = today.toLocaleDateString('en-US', options);
    });
</script>
</body>
</html>
