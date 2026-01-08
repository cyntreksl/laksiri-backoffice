<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Baggage Receipts</title>
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

        .page-break {
            page-break-after: always;
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
@foreach($hbls as $index => $hbl)
    @if($index > 0)
        <div class="page-break"></div>
    @endif
    
    <div class="header">
        <div>
            <div style="align-items: center"></div>
            <div></div>
        </div>
    </div>

    <table class="details-table">
        <tr>
            <td class="label">MHBL Number:</td>
            <td>{{ $container->bl_number ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">HBL Number:</td>
            <td>{{ $hbl->hbl_number ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Reference:</td>
            <td>{{ $hbl->reference ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Date:</td>
            <td><?php echo date('d/m/Y'); ?></td>
        </tr>
        <tr>
            <td class="label">Shipper Name:</td>
            <td>{{ $hbl->hbl_name ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Shipper Contact:</td>
            <td>{{ $hbl->contact_number ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Package Count:</td>
            <td>{{ $hbl->packages->count() ?? 0 }}</td>
        </tr>
        <tr>
            <td class="label">BL Number:</td>
            <td>{{ $container->bl_number ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Consignee Name:</td>
            <td>{{ $hbl->consignee_name ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Cargo Type:</td>
            <td>{{ $hbl->cargo_type ?? 'P/E' }}</td>
        </tr>
        <tr>
            <td class="label">Port of Discharge:</td>
            <td>{{ $container->port_of_discharge ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Warehouse:</td>
            <td>{{ $hbl->warehouse ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Vessel Name:</td>
            <td>{{ $container->vessel_name ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Company:</td>
            <td>{{ $settings['invoice_header_title'] ?? '' }}</td>
        </tr>
    </table>
@endforeach
</body>
</html>
