@extends('pdf.main')

@section('pdf-content')

    <style>
        * {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .table-row {
            border-bottom: 2px solid black;
            border-collapse: collapse;
        }

    </style>

    @php

        $count=count($hbl->packages);

    @endphp

    @foreach ($hbl->packages as $package)

        <table  class="table-row" >
            <tbody>
            <tr>
                <td width="40%" >{{ $hbl->created_at }}</td>
                <td width="40%">{{ $hbl->hbl_number }}</td>
                <td width="20%">{{$hbl->branch?->email }}</td>
            </tr>
            </tbody>
        </table>

        <table  class="table-row"  >
            <tbody>
            <tr>
                <td width="30%" style="text-align: left; vertical-align: middle;">
                    @if($logoPath)
                        <img
                            style="max-width: 70px; max-height: 40px; display: inline-block; vertical-align: middle;"
                            src="{{ $logoPath }}"
                            alt="app_logo">
                    @endif
                </td>
                <td width="60%" style="text-align: center; font-size: 20px; vertical-align: middle;">
                    {{$hbl?->cargo_type}} / {{$hbl?->hbl_type}}
                </td>
                <td width="20%">PACKAGES:
                    <table style="margin-top: -10px; ">
                        <tr>
                            <td><label style="margin-left: -12px; font-size: 25px; text-transform: capitalize" >{{ $loop->iteration }}/{{$count}}</label></td>
                        <tr>
                    </table>

                </td>
            </tr>
            </tbody>
        </table>

        <table  class="table-row"  >
            <tbody>
            <tr>
                <td style="text-align: left;" >From:<br>
                    {{ $hbl->hbl_name}}<br>{{ $hbl->address }}<br>{{ $hbl->contact_number }}<br>
                </td>
            </tr>
            </tbody>
        </table>

        <table  class="table-row"   >
            <tbody>
            <tr>
                <td style="text-align: left;" >To:<br>
                    {{ $hbl->consignee_name }}<br>{{ $hbl->consignee_address }}<br>{{ $hbl->consignee_contact }} {{ $hbl->consignee_additional_mobile_number ?  '/ '.$hbl->consignee_additional_mobile_number : ''}}<br>

                </td>
            </tr>
            </tbody>
        </table>

        <table  class="table-row" >
            <tbody>
            <tr>
                <td width="50%">ORIGIN:
                    <table style="margin-top: -10px; ">
                        <tr>
                            <td><label style="margin-left: -12px; font-size: 25px; text-transform: capitalize" >{{ $hbl->branch->name }}</label></td>
                        <tr>
                    </table>

                </td>
                <td width="50%">DESTINATION:
                    <table style="margin-top: -10px; ">
                        <tr>
                            <td>  <label style="margin-left: -12px; font-size: 25px;" >{{ $hbl->warehouse }}</label></td>
                        <tr>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>

        <table  class="table-row" >
            <tbody>
            <tr>
                <td>WEIGHT:
                    <br>
                    <label  style="margin-left: -5px; font-size: 15px" >{{$package?->weight}}</label>
                </td>
                <td>VOLUME:<br>
                    <label  style="margin-left: -5px; font-size: 15px" >{{$package?->volume}}</label>
                </td>

            </tr>
            </tbody>
        </table>

        <table >
            <tbody>
            <tr>
                <td width="50%">HBL:
                    <table style="margin-top: -10px; ">
                        <tr>
                            <td><label  style="margin-left: -12px; font-size: 25px" >
                                    {{ $hbl->hbl_number }}</label></td>
                        <tr>
                    </table>

                </td>
                <td width="50%">PID:
                    <table style="margin-top: -10px; ">
                        <tr>
                            <td><label  style="margin-left: -12px; font-size: 20px" >
                                    {{$package?->package_type}}</label></td>
                        <tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>MHBL:
                    <table  style="margin-top: -10px; ">
                        <tr>
                            <td><label  style="margin-left: -12px; font-size: 25px" >
                                    {{ $mhbl ?? '' }}</label></td>
                        <tr>
                    </table>
                </td>

            </tr>
            </tbody>
        </table>

        <table class="table-row" style="margin-top: -20px">
            <tbody>
            <tr>
                <td>
                </td>
            </tr>
            </tbody>
        </table>

        <table>
            <tbody>
            <tr>
                <td style="text-align: center;">
                    @php
                        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
    echo '<img   style="height: 100px;"        width="80%" src="data:image/png;base64,' . base64_encode($generator->getBarcode($hbl->hbl_number, $generator::TYPE_CODE_128, 3, 80)) . '">';
                    @endphp
                </td>
            </tr>
            </tbody>
        </table>

    @endforeach
@endsection

