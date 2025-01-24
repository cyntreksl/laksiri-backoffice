<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Baggage Receipt</title>
    <style>
        @page {
            size: A4;
            margin: 20px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            line-height: 1.6;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .title {
            text-align: center;
            font-size: 24px;
            margin: 25px 0;
            text-decoration: underline;
        }

        .details-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .details-table td {
            padding: 8px 0;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            min-width: 150px;
        }

        .section {
            margin: 15px 0;
        }

        .split-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .border-bottom {
            border-bottom: 1px solid black;
        }
    </style>
</head>
<body>
<div class="header">

    <div>
        <div>Serial No. :</div>
        <div>Baggage Receipt No. :</div>
    </div>
    <div>Date : 09/01/2025</div>
</div>

<h1 class="title">BAGGAGE RECEIPT</h1>

<table class="details-table">
    <tr>
        <td class="label">Bond Storage No. :</td>
        <td>01/2554-2554</td>
    </tr>
    <tr>
        <td class="label">Airway Bill No. :</td>
        <td>APU097099</td>
    </tr>
    <tr>
        <td class="label">B/L No. :</td>
        <td>{{$containers->bl_number ?? ''}}</td>
    </tr>
    <tr>
        <td class="label">Consignee :</td>
        <td>{{$hbl->consignee_name ?? ''}}</td>
    </tr>
    <tr>
        <td class="label">No. of Packages :</td>
        <td>P/E</td>
    </tr>
    <tr>
        <td class="label">PORT :</td>
        <td>{{$containers->port_of_discharge ?? ''}}</td>
    </tr>
    <tr>
        <td class="label">VESSEL :</td>
        <td>{{$containers->vessel_name ?? ''}}</td>
    </tr>
    <tr>
        <td class="label">AGENT :</td>
        <td>{{$settings->invoice_header_title ?? ''}}</td>
    </tr>
</table>

</body>
</html>
