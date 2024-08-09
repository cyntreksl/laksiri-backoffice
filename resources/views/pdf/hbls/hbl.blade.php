@extends('pdf.main')

@section('pdf-header')
    @parent
    @include('pdf.hbls.partials.header')
@endsection

@section('pdf-content')
    <table>
        <tbody>
        <tr>
            <td style="width: 40%;">
                <table>
                    <tbody>
                    <tr>
                        <td>Date:</td>
                        <td>{{ $hbl->created_at->toDateString() }}</td>
                    </tr>
                    <tr>
                        <td>Time:</td>
                        <td>{{ $hbl->created_at->toTimeString() }}</td>
                    </tr>
                    <tr>
                        <td>Cargo Type:</td>
                        <td>{{$hbl?->cargo_type}} / {{$hbl?->hbl_type}}</td>
                    </tr>
                    <tr>
                        <td>Final Destination:</td>
                        <td>{{$hbl?->warehouse}}</td>
                    </tr>
                    <tr>
                        <td>HBL:</td>
                        <td>{{$hbl?->hbl}}</td>
                    </tr>
                    </tbody>
                </table>
            </td>
            <td style="vertical-align: top; width: 60%">
                <div>
                    <img style="width: 25%; margin-right: -30px; float: left" src="{{public_path('images/app-logo.png')}}"
                         alt="app_logo">
                    <div style="text-align: center; line-height: 1">
                        <h3 style="text-decoration: underline; margin: 0; padding: 0">DELIVERY AGENT SRI LANKA</h3>
                        <h4 style="font-weight: bold; margin: 2px 0 0;padding: 0">LAKSIRI SEVA (PVT) LTD</h4>
                        <p style="margin: 0; padding: 0">NO. 31, ST. ANTHONY'S MW, COLOMBO-03 <br>
                            TEL: +94 112574180, +94 112575576 | FAX: +94 112575576 <br>
                            E-mail: laksiriseva@laksirigroup.com | Web: www.laksirigroup.com</p>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

    <table style="margin-top: 0;">
        <tr>
            <td rowspan="2" style="width: 50%">
                @php
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($hbl->hbl, $generator::TYPE_CODE_128, 3, 80)) . '">';
                @endphp
            </td>
            <td colspan="2" style="text-align: center; font-weight: bold; width: 50%">
                U.P.B BONDED WAREHOUSE
            </td>
        </tr>
        <tr>
            <td style="width: 25%">
                <span style="font-weight:bold;">COLOMBO</span>
                <p style="padding-top: 0; margin-top: 0">
                    LAKSIRI SEVA (PVT) LTD, <br>
                    NO. 66, NEW NUGE ROAD, <br>
                    PELIYAGODA. <br>
                    TEL: +94 112917729 / 31
                </p>
            </td>
            <td style="width: 25%">
                <span style="font-weight:bold;">NINTHAVUR</span>
                <p style="padding-top: 0; margin-top: 0">
                    LAKSIRI SEVA (PVT) LTD, <br>
                    NO. 26, H.M.Y.L LANE, MAIN STREET <br>
                    ADDAPPALLAM, NINTHAVUR. <br>
                    TEL: +94 672051050 / 51
                </p>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td style="border: 1px solid black; border-collapse: collapse; height: 70px">
                <table class="shipper-table">
                    <tr>
                        <td style="font-weight: bold">SHIPPER DETAILS</td>
                    </tr>
                    <tr>
                        <td>NAME:</td>
                        <td>{{ $hbl?->hbl_name }}</td>
                    </tr>
                    <tr>
                        <td>ADDRESS:</td>
                        <td>{{ $hbl?->address }}</td>
                    </tr>
                    <tr>
                        <td>IQ ID:</td>
                        <td>{{ $hbl?->iq_number }}</td>
                    </tr>
                    <tr>
                        <td>TEL:</td>
                        <td>{{ $hbl?->contact_number }}</td>
                    </tr>
                    <tr>
                        <td>ID / PP NO:</td>
                        <td>{{ $hbl?->nic }}</td>
                    </tr>
                </table>
            </td>
            <td style="border: 1px solid black; border-collapse: collapse; height: 70px">
                <table class="consignee-table">
                    <tr>
                        <td style="font-weight: bold">CONSIGNEE DETAILS</td>
                    </tr>
                    <tr>
                        <td>NAME:</td>
                        <td>{{ $hbl?->consignee_name }}</td>
                    </tr>
                    <tr>
                        <td>ADDRESS:</td>
                        <td>{{ $hbl?->consignee_address }}</td>
                    </tr>
                    <tr>
                        <td>TEL:</td>
                        <td>{{ $hbl?->consignee_contact }}</td>
                    </tr>
                    <tr>
                        <td>ID / PP NO:</td>
                        <td>{{ $hbl?->consignee_nic }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="package-table">
        <tr>
            <th>S/N</th>
            <th>Description</th>
            <th>Size</th>
            <th>QTY</th>
            <th>Weight</th>
            <th>Volume</th>
            <th>Remarks</th>
        </tr>
        @forelse($hbl->packages as $package)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$package?->package_type}}</td>
                <td>{{$package?->length }} X {{$package?->width }} X {{$package?->height }}</td>
                <td>{{$package?->quantity}}</td>
                <td>{{round($package?->weight, 2)}}</td>
                <td>{{round($package?->volume, 3)}}</td>
                <td>{{$package?->remarks ?? '-'}}</td>
            </tr>
        @empty
            <tr style="text-align: center">
                <td colspan="7">No Any Package</td>
            </tr>
        @endforelse
    </table>

    <table>
        <tr>
            <td>NO. OF PCS:</td>
            <td>{{$hbl?->packages->count()}}</td>

            <td>FREIGHT CHARGES</td>
            <td>{{number_format($hbl?->freight_charge, 2)}}</td>

            <td>DESTI. CHARGES</td>
            <td>{{number_format($hbl?->other_charge, 2)}}</td>
        </tr>
        <tr>
            <td>TOTAL VOLUME</td>
            <td>{{round($hbl?->packages->sum('volume'), 3)}}</td>

            <td>HBL CHARGE</td>
            <td>{{number_format($hbl?->bill_charge, 2)}}</td>

            <td>GRAND TOTAL</td>
            <td>{{number_format($hbl?->grand_total, 2)}}</td>
        </tr>
        <tr>
            <td>TOTAL WEIGHT</td>
            <td>{{round($hbl?->packages->sum('weight'), 2)}}</td>

            <td>HBL VAT CHARGE</td>
            <td>-</td>

            <td>PAID AMOUNT</td>
            <td>{{number_format($hbl?->paid_amount, 2)}}</td>
        </tr>
    </table>

    <table class="in-words-table">
        @php
            $a = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
        @endphp
        <tr>
            <td style="font-weight: bold;">IN WORDS:</td>
            <td style="text-transform: uppercase">{{$a->format($hbl?->grand_total)}} only</td>
        </tr>
    </table>
@endsection

@section('pdf-footer')
    @parent
    @include('pdf.hbls.partials.footer')
@endsection
