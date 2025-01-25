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
        <div style="align-items: center">                  </div>
        <div>                </div>
    </div>

</div>



<table class="details-table">
    <tr>
        <td class="label">     </td>
        <td>          MF0125455 </td>
    </tr>
    <tr>
        <td class="label">     </td>
        <td>     </td>
    <tr>
    <tr>
        <td class="label">     </td>
        <td>     </td>
    <tr>

    <tr>
        <td class="label">     </td>
        <td><?php echo date('d/m/Y'); ?> </td>
    </tr>
    <tr>
        <td class="label">     </td>
        <td>     </td>
    <tr>
    <tr>
        <td class="label">     </td>
        <td>     </td>
    <tr>

    <tr>
        <td class="label">     </td>
        <td>01/2554-2554</td>
    </tr>
    <tr>
        <td class="label">     </td>
        <td>APU097099</td>
    </tr>
    <tr>
        <td class="label">     </td>
        <td>{{$containers->bl_number ?? ''}}</td>
    </tr>
    <tr>
        <td class="label">     </td>
        <td>     </td>
    <tr>
        <td class="label">     </td>
        <td>{{$hbl->consignee_name ?? ''}}</td>
    </tr>
    <tr>
        <td class="label">     </td>
        <td>P/E</td>
    </tr>
    <tr>
        <td class="label">     </td>
        <td>{{$containers->port_of_discharge ?? ''}}</td>
    </tr>
    <tr>
        <td class="label">     </td>
        <td>     </td>
    <tr>
    <tr>
        <td class="label">     </td>
        <td>{{$containers->vessel_name ?? ''}}</td>
    </tr>
    <tr>
        <td class="label">     </td>
        <td>{{$settings->invoice_header_title ?? ''}}</td>
    </tr>
    <tr>
        <td class="label">     </td>
        <td>     </td>
    <tr>
    <tr>
        <td class="label">     </td>
        <td>     </td>
    <tr>
    <tr>
        <td class="label">     </td>
        <td>          chef</td>
    <tr>
</table>

</body>
</html>
