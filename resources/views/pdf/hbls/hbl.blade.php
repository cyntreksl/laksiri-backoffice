@extends('pdf.main')

@section('pdf-content')
    <style>
        td {
            font-family: "Roboto", sans-serif;
            font-size: 11.5px;
            padding: 2px;
        }

        td .hbl-title {
            color: #636363;
        }

        .page-break {
            page-break-after: always;
        }

        .summary {
            margin-top: 20px;
            border-top: 1px solid black;
            width: 100%;
        }

        .footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
        }
    </style>

    @php
        $packagesChunks = $hbl->packages->chunk(15); // Chunking packages to fit on each page
        $serialNumber = 1; // Initialize serial number
    @endphp

    {{-- Iterate through each chunk of packages --}}
    @foreach ($packagesChunks as $chunk)
        {{-- Include header on every page --}}
        @include('pdf.hbls.partials.header')

        <table class="cargo-detals" style="margin-bottom: -20px;">
            <tbody>
                <tr>
                    <td style="width: 40%;">
                        <table>
                            <tbody>
                                <tr>
                                    <td class="hbl-title">Date:</td>
                                    <td>{{ $hbl->created_at->toDateString() }}</td>
                                </tr>
                                <tr>
                                    <td class="hbl-title">Time:</td>
                                    <td>{{ $hbl->created_at->toTimeString() }}</td>
                                </tr>
                                <tr>
                                    <td class="hbl-title">Cargo Type:</td>
                                    <td>{{ $hbl?->cargo_type }} / {{ $hbl?->hbl_type }}</td>
                                </tr>
                                <tr>
                                    <td class="hbl-title">Final Destination:</td>
                                    <td>{{ $hbl?->warehouse }}</td>
                                </tr>
                                <tr>
                                    <td class="hbl-title">HBL:</td>
                                    <td>{{ $hbl?->hbl }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td style="vertical-align: top; width: 60%">
                        <div>
                            <img style="width: 30%; margin-right: -80px; float: left; margin-top: -30px"
                                src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/app-logo.png'))) }}"
                                alt="app_logo">
                            <div style="text-align: center; line-height: 1">
                                <h4 style="text-decoration: underline; margin: 0; padding: 0">DELIVERY AGENT SRI LANKA</h4>
                                <h4 style="font-weight: bold; margin: 2px 0 0;padding: 0">LAKSIRI SEVA (PVT) LTD</h4>
                                <p style="margin: 0; padding: 0; font-family: 'Roboto', sans-serif; color: #636363">NO. 31,
                                    ST.
                                    ANTHONY'S MW, COLOMBO-03 <br>
                                    TEL: +94 112574180, +94 112575576 | FAX: +94 112575576 <br>
                                    E-mail: laksiriseva@laksirigroup.com | Web: www.laksirigroup.com
                                </p>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="barcode" style="margin-top: 0; margin-bottom: -16px; width: 100%;">
            <tr>
                <td rowspan="3" style="width: 39%; vertical-align: middle;">
                    @php
                        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                        echo '<img style="width: 90%" src="data:image/png;base64,' .
                            base64_encode($generator->getBarcode($hbl->hbl, $generator::TYPE_CODE_128, 3, 80)) .
                            '">';
                    @endphp
                </td>
                <td colspan="3" style="width: 61%; text-align: center; font-weight: bold; text-decoration: underline;">
                    U.P.B BONDED WAREHOUSE
                </td>
            </tr>
            <tr>
                <td rowspan="2" style="width: 18%; vertical-align: top;">
                    <span style="font-weight:bold;">KURUNEGALA</span>
                    <p style="padding-top: 0; margin-top: 0; font-size: xx-small;">
                        BUS STATION, <br>
                        ALUTHMALKADUWAWA, <br>
                        KURUNEGALA. <br>
                        TEL: +94 112917729
                    </p>
                </td>
                <td rowspan="2" style="width: 20%; vertical-align: top;">
                    <span style="font-weight:bold;">COLOMBO</span>
                    <p style="padding-top: 0; margin-top: 0; font-size: xx-small;">
                        LAKSIRI SEVA (PVT) LTD, <br>
                        NO. 66, NEW NUGE ROAD, <br>
                        PELIYAGODA. <br>
                        TEL: +94 112917729 / 31
                    </p>
                </td>
                <td rowspan="2" style="width: 23%;  vertical-align: top;">
                    <span style="font-weight:bold;">NINTHAVUR</span>
                    <p style="padding-top: 0; margin-top: 0; font-size: xx-small;">
                        LAKSIRI SEVA (PVT) LTD, <br>
                        NO. 26, H.M.Y.L LANE, MAIN ST <br>
                        ADDAPPALLAM, NINTHAVUR. <br>
                        TEL: +94 672051050 / 51
                    </p>
                </td>
            </tr>
        </table>

        {{-- Include shipping details only on the first page --}}
        @if ($loop->first)
            <table class="shipping-details" style="margin-top: 0;">
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
        @endif

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
            @forelse($chunk as $package)
                <tr>
                    <td>{{ $serialNumber++ }}</td>
                    <td>{{ $package?->package_type }}</td>
                    <td>{{ $package?->length }} X {{ $package?->width }} X {{ $package?->height }}</td>
                    <td>{{ $package?->quantity }}</td>
                    <td>{{ round($package?->weight, 2) }}</td>
                    <td>{{ round($package?->volume, 3) }}</td>
                    <td>{{ $package?->remarks ?? '-' }}</td>
                </tr>
            @empty
                <tr style="text-align: center">
                    <td colspan="7">No Any Package</td>
                </tr>
            @endforelse

            {{-- Add empty rows if it's the first page and less than 15 packages --}}
            {{-- @if ($chunk->count() < 15)
                @for ($i = 0; $i < 15 - $chunk->count(); $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            @endif --}}
        </table>

        {{-- Add the summary table only before the footer on the last page --}}
        @if ($loop->last)
            <table class="summary">
                <tr>
                    <td>NO. OF PCS:</td>
                    <td>{{ $hbl?->packages->count() }}</td>

                    <td>FREIGHT CHARGES</td>
                    <td>{{ number_format($hbl?->freight_charge, 2) }}</td>

                    <td>DESTI. CHARGES</td>
                    <td>{{ number_format($hbl?->other_charge, 2) }}</td>
                </tr>
                <tr>
                    <td>TOTAL VOLUME</td>
                    <td>{{ round($hbl?->packages->sum('volume'), 3) }}</td>

                    <td>HBL CHARGE</td>
                    <td>{{ number_format($hbl?->bill_charge, 2) }}</td>

                    <td>GRAND TOTAL</td>
                    <td>{{ number_format($hbl?->grand_total, 2) }}</td>
                </tr>
                <tr>
                    <td>TOTAL WEIGHT</td>
                    <td>{{ round($hbl?->packages->sum('weight'), 2) }}</td>

                    <td>HBL VAT CHARGE</td>
                    <td>-</td>

                    <td>PAID AMOUNT</td>
                    <td>{{ number_format($hbl?->paid_amount, 2) }}</td>
                </tr>
            </table>
        @endif

        {{-- Include footer on every page --}}
        @include('pdf.hbls.partials.footer')

        {{-- Add a page break after each chunk, except the last one --}}
        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
@endsection
