<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Invoice - Sea Cargo</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="p-4">
<div class="mx-auto max-w-7xl">
    <div class="grid grid-cols-2 gap-4">
        <!-- LEFT COLUMN - INVOICE -->
        <div class="pr-4">
            <!-- Header -->
            <div class="mb-2 flex items-start gap-3">
                <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center invisible">
                    <img src="{{$logoPath}}" alt="Logo" class="h-full w-full object-contain" />
                </div>
                <div class="flex-1">
                    <div class="mb-1 text-right text-xs font-bold">CASHIER'S COPY</div>
                    <h1 class="text-sm font-bold leading-tight invisible">LAKSIRI INTERNATIONAL FREIGHT FORWARDERS (PVT) LTD.</h1>
                    <p class="text-xs font-bold invisible">U.P.B. Warehouse</p>
                    <p class="text-xs invisible">66, NEW NUGE ROAD,</p>
                    <p class="text-xs invisible">PELIYAGODA</p>
                    <p class="text-xs invisible">V.A.T. Reg. No. : 114162671-7000</p>
                    <p class="text-xs invisible">TEL : 2917730-1, 2917890-92 Fax : 2917732</p>
                    <p class="text-xs invisible">E-mail : lakbond@laksirigroup.com</p>
                </div>
            </div>

            <div class="mb-2 flex justify-between text-xs">
                <div><span class="invisible">Clearing Time : </span><span class="text-gray-800">{{ $data['clearing_time'] }}</span></div>
                <div><span class="invisible">Vehicle No :</span></div>
            </div>

            <div class="mb-2 flex justify-between text-xs">
                <div><span class="invisible">Date : </span><span class="text-gray-800">{{ $data['date'] }}</span></div>
                <div><span class="invisible">Serial No. : </span><span class="text-gray-800">IN00708920</span></div>
            </div>

            <!-- Title -->
            <div class="mb-2 py-1 text-center invisible">
                <h2 class="text-sm font-bold">U.P.B. WAREHOUSE INVOICE FOR <span class="underline">SEA CARGO</span></h2>
            </div>

            <!-- Customer Details -->
            <div class="mb-2 space-y-1 text-xs">
                <div class="flex">
                    <span class="invisible w-40">Name of Consignee</span>
                    <span class="text-gray-800">{{$data['hbl']['consignee_name']}}</span>
                </div>
                <div class="flex">
                    <span class="invisible w-40">B/L No.</span>
                    <span class="text-gray-800">{{ $data['vessel']['bl_number'] ?? ''}}</span>
                </div>
                <div class="flex">
                    <span class="invisible w-40">Vessel</span>
                    <span class="text-gray-800">{{ $data['vessel']['vessel_name'] ?? '' }}</span>
                </div>
                <div class="flex">
                    <span class="invisible w-40">Passport / I.D. Card No.</span>
                    <span class="text-gray-800">{{$data['hbl']['nic']}}</span>
                </div>
                <div class="flex">
                    <span class="invisible w-40">No. of Packages</span>
                    <span class="text-gray-800">{{ count($data['hbl']['packages']) }}</span>
                </div>
                <div class="flex">
                    <span class="invisible w-40">Bond Storage No.</span>
                    <span class="text-gray-800">@if(!empty($data['bond_storage_numbers'])){{ implode(', ', $data['bond_storage_numbers']) }}@else N/A @endif</span>
                </div>
                <div class="flex">
                    <span class="invisible w-40">Agent</span>
                    <span class="text-gray-800">{{ $data['hbl']['branch']['name'] }}</span>
                </div>
                <div class="flex">
                    <span class="invisible w-40">Volume</span>
                    <span class="text-gray-800">{{ $data['grand_volume'] }}</span>
                </div>
            </div>

            <!-- Charges Table -->
            <div class="mb-2">
                <table class="w-full border-collapse text-xs">
                    <thead>
                    <tr class="bg-gray-200">
                        <th class="px-2 py-1 text-left font-bold text-gray-600 invisible">DESCRIPTION</th>
                        <th class="px-2 py-1 text-center font-bold text-gray-600 invisible">RATE</th>
                        <th class="px-2 py-1 text-right font-bold text-gray-600 invisible">AMOUNT</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="px-2 py-1 invisible">Sri Lanka Port Charges - Sea Cargo</td>
                        <td class="px-2 py-1 text-center text-gray-800">{{ number_format($data['charges']['port_charge']['rate'],2) }}</td>
                        <td class="px-2 py-1 text-right text-gray-800">{{ number_format($data['charges']['port_charge']['amount'],2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1"></td>
                        <td class="px-2 py-1 text-center text-gray-800">0.00</td>
                        <td class="px-2 py-1 text-right text-gray-800">0.00</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1 invisible">
                            Handling Charges<br />
                            <span class="text-[10px]">( Per Package )</span>
                        </td>
                        <td class="px-2 py-1 text-center text-gray-800">{{ number_format($data['charges']['handling_charge']['rate'],2) }}</td>
                        <td class="px-2 py-1 text-right text-gray-800">{{ number_format($data['charges']['handling_charge']['amount'],2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1 invisible">
                            Bond Storage Charges<br />
                            <span class="text-[10px]">( Per Cubic Foot )</span>
                        </td>
                        <td class="px-2 py-1 text-center text-gray-800">{{ number_format($data['charges']['storage_charge']['rate'],2) }}</td>
                        <td class="px-2 py-1 text-right text-gray-800">{{ number_format($data['charges']['storage_charge']['amount'],2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1 invisible">
                            Demurrage Charges<br />
                            <span class="text-[10px]">( Per Cubic Foot )</span>
                        </td>
                        <td class="px-2 py-1 text-center text-gray-800">
                            9.50<br />
                            10.00
                        </td>
                        <td class="px-2 py-1 text-right text-gray-800">{{ number_format($data['charges']['dmg_charge']['amount'],2) }}</td>
                    </tr>
                    <tr class="font-bold">
                        <td class="px-2 py-1 invisible">TOTAL</td>
                        <td class="px-2 py-1"></td>
                        <td class="px-2 py-1 text-right text-gray-800">{{ number_format($data['charges']['total'],2)}}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Payment Summary -->
            <div class="mb-2 text-xs">
                <div class="mb-1">
                    <span class="font-bold invisible">Rupees :</span>
                    <div class="ml-12">
                        <div><span class="">D/O Chrgs. </span><span class="float-right text-gray-800">{{ number_format($data['charges']['do_charge'],2) }}</span></div>
                        <div><span class="">Stamp Duty </span><span class="float-right text-gray-800">{{ number_format($data['charges']['stamp_charge'],2) }}</span></div>
                        <div class="font-bold"><span class="">G/Total : </span><span class="float-right text-gray-800">{{ number_format($data['charges']['total']+$data['charges']['do_charge']+$data['charges']['stamp_charge'],2) }}</span></div>
                    </div>
                </div>
                @if (!empty($data['taxes']))
                <div class="text-[10px]">
                    <span class="">VAT 18% included in the charges</span><br />
                    <span class="text-gray-800">{{ strtoupper($data['total_in_word']) }}</span>
                </div>
                @endif
            </div>

            <!-- Signature Section -->
            <div class="mb-2 mt-8 grid grid-cols-2 gap-4">
                <div>
                    <div class="mb-8 text-xs invisible">Cashier - Amount Received</div>
{{--                    <div class="border-b border-black"></div>--}}
                    <div class="text-center text-xs invisible">Signature :</div>
                </div>
                <div>
                    <div class="mb-8 text-xs">&nbsp;</div>
{{--                    <div class="border-b border-black"></div>--}}
                    <div class="text-center text-xs invisible">Signature</div>
                </div>
            </div>

            <!-- Footer Note -->
            <div class="mt-4 border-t-2 border-black pt-2 text-center text-[10px] font-bold invisible">
                NOTE : WE DO NOT TAKE ANY RESPONSIBILITY FOR ANY LOSS OR DAMAGE<br />
                TO THE GOODS, AFTER CLEARING THE CARGO.
            </div>
        </div>

        <!-- RIGHT COLUMN - GATE PASS -->
        <div class="pl-4">
            <!-- Header -->
            <div class="mb-2 invisible">
                <div class="mb-1 flex justify-between text-xs">
                    <div>
                        <p class="font-bold">Head Office :</p>
                        <p>NO. 66, ANTHONY'S MAWATHA,</p>
                        <p>COLOMBO 03, SRI LANKA.</p>
                        <p>Tel : 2577576, 2574180,</p>
                        <p>2574181, 2574182, 2574185 ></p>
                        <p>Attn: Laksara Fax : 2577737</p>
                        <p>Email:laksiri@laksirigroup.com</p>
                    </div>
                    <div>
                        <p class="font-bold">Nintavur Branch :</p>
                        <p>U.P.D. HOUSE,</p>
                        <p>103/6, MAIN STREET,</p>
                        <p>ADDAPALLIAN, NINTAVUR</p>
                        <p>Tel : 067 2050500, 2050501</p>
                        <p>Fax : 067 2050552</p>
                        <p>E-mail:upb@laksirigroup.com</p>
                    </div>
                </div>
                <p class="text-center text-xs font-bold">V.A.T. Reg. No. : 114162671-7000</p>
            </div>

            <div class="mb-2 flex justify-between text-xs">
                <div><span class="invisible">Gate Pass : </span><span class="text-gray-800">0.00</span></div>
            </div>

            <!-- Title -->
            <div class="mb-2 border-y-2 border-black py-1 text-center invisible">
                <h2 class="text-sm font-bold"><span class="underline">SEA CARGO</span></h2>
            </div>

            <!-- Gate Pass Details -->
            <div class="mb-2 space-y-1 text-xs">
                <div class="flex">
                    <span class="invisible w-40">No.</span>
                    <span class="text-gray-800">IN00708920</span>
                </div>
                <div class="flex">
                    <span class="w-40">Dostuf Date .</span>
                    <span class="text-gray-800">{{ $data['date'] }}</span>
                </div>
                <div class="flex">
                    <span class="invisible w-40">B/L No.</span>
                    <span class="text-gray-800">{{ $data['vessel']['bl_number'] ?? ''}}</span>
                </div>
                <div class="flex">
                    <span class="invisible w-40">Description of goods</span>
                    <span class="text-gray-800">BUNDLES FRIDGE SOFA SET</span>
                </div>
                <div class="flex">
                    <span class="invisible w-40"></span>
                    <span class="text-gray-800">J-VEARITON</span>
                </div>
                <div class="flex">
                    <span class="invisible w-40">No. of Packages</span>
                    <span class="text-gray-800">{{ count($data['hbl']['packages']) }}</span>
                </div>
                <div class="flex">
                    <span class="invisible w-40">Passport / I.D. Card No.</span>
                    <span class="text-gray-800"></span>
                </div>
                <div class="flex">
                    <span class="invisible w-40">Bond Storage No.</span>
                    <span class="text-gray-800">@if(!empty($data['bond_storage_numbers'])){{ implode(', ', $data['bond_storage_numbers']) }}@else N/A @endif</span>
                </div>
                <div class="flex">
                    <span class="invisible w-40">Vessel</span>
                    <span class="text-gray-800">{{ $data['vessel']['vessel_name'] ?? '' }}</span>
                </div>
                <div class="flex">
                    <span class="invisible w-40">Delivered from</span>
                    <span class="text-gray-800"></span>
                </div>
            </div>

            <div class="mb-2 border border-gray-400 p-2 text-xs invisible">
                The above goods may be permitted to be removed
            </div>

            <div class="mb-2 space-y-1 text-xs">
                <div class="flex">
                    <span class="invisible w-20">BY</span>
                    <span class="text-gray-800">{{$data['hbl']['consignee_name']}}</span>
                </div>
                <div class="flex">
                    <span class="invisible w-20">Date</span>
                    <span class="text-gray-800">{{ $data['date'] }}</span>
                </div>
                <div class="flex">
                    <span class="invisible w-20">Time</span>
                    <span class="text-gray-800">{{ $data['clearing_time'] }}</span>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="mb-2 mt-12 text-right">
                <div class="mb-8 text-xs">&nbsp;</div>
                <div class="ml-auto w-48 border-b invisible"></div>
                <div class="text-xs invisible">Officer</div>
            </div>

            <!-- Footer -->
            <div class="mt-8 space-y-1 text-xs">
                <div class="flex">
                    <span class="invisible w-32">Reference No. :</span>
                    <span class="text-gray-800">MF00020377</span>
                </div>
                <div class="flex">
                    <span class="invisible w-32">Serial No.</span>
                    <span class="text-gray-800">23</span>
                </div>
                <div class="flex">
                    <span class="invisible w-32">User ID</span>
                    <span class="text-gray-800">KALIMAN</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @page {
        size: letter portrait;
    }
    body {
        font-family: Arial, sans-serif;
        color: #6b7280;
    }
</style>
</body>
</html>
