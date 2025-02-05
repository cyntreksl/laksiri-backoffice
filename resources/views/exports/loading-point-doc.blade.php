<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Loading Point Document </title>
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
            flex-direction: column; /* Stack elements vertically */
        }

        .name {
            display: flex;
            flex-direction: column; /* Stack elements vertically */
            margin-left: 70px; /* Adjust as needed */
            margin-top: 5px;
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
    $itemsPerPage = 20; // Number of rows per page
    $chunks = array_chunk($hbls->toArray(), $itemsPerPage);
@endphp

@foreach ($chunks as $chunkIndex => $chunk)
    <table>
            <thead>
                @if ($chunkIndex === 0)
                    <tr>
                        <th colspan="10" style="text-align:center;">
                            <strong><em>
                                    LOADING POINT DOCUMENT
                                </em></strong>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="10" style="text-align:center;">
                            <strong><em>{{ $container->cargo_type }}</em></strong>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">CONTAINER NO: <br/>{{$container?->container_number}}</th>
                        <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 14px;"><p>CONTAINER TYPE: <br/>{{$container?->container_type}}</p></th>
                        <th colspan="2" style="font-family: 'Times New Roman',fantasy; font-size: 14px;">SEAL NO: <br/>{{$container?->seal_number}}</th>
                        <th colspan="2" style="font-family: 'Times New Roman',fantasy; font-size: 14px;"><p>BL NO: <br/>{{$container?->bl_number}}</p></th>
                    </tr>
                <@endif
                <tr style="font-family: 'Times New Roman',fantasy; font-size: 14px; background-color: #D8D8D8  ;">
                    <th colspan="1" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">SR NO</th>
                    <th colspan="1">HBL NO</th>
                    <th colspan="3" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">TYPE OF PKGS</th>
                    <th colspan="1" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">NO.OF <br/>PKGS</th>
                    <th colspan="1" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">VOLUME <br/>CBM</th>
                    <th colspan="1" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">GWHT</th>
                    <th colspan="2" style="font-family: 'Times New Roman',fantasy; font-size: 10px;">Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($chunk as $index => $item)
                    <tr>
                        @php
                            $hblPackages = $container->hbl_packages->where('hbl_id', $item['id']);
                        @endphp
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item['hbl_number'] ?? $item['reference'] }}</td>
                        <td colspan="3">
                            @foreach($hblPackages as $package)
                                {{ $package['quantity'] }} - {{ $package['package_type'] }} <br/>
                            @endforeach
                        </td>
                        <td colspan="1">
                            {{COUNT($hblPackages)}}
                        </td>
                        <td colspan="1">
                            {{ $hblPackages->sum('volume') }}
                        </td>
                        <td colspan="1">
                            {{ $hblPackages->sum('weight') }}
                        </td>
                        <td colspan="2">
                        </td>
                    </tr>
                @endforeach
            </tbody>
    </table>
    <div class="footer">
        <div class="footer-text"  style="font-family: 'Italic Outline Art', sans-serif; font-style: italic;">{{$settings?->invoice_header_title}}</div>
        <span class="page-number">Page: </span>
    </div>
    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
