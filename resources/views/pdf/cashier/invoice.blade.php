<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Invoice - Sea Cargo</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="min-h-screen p-4">
<div class="mx-auto max-w-5xl overflow-hidden text-blue-900">
    <!-- Header -->
    <div class="p-6">
        <p class="text-right text-xs font-semibold text-neutral-400">CASHIER'S COPY - (#{{$data['hbl']['id']}})</p>

        <div class="flex items-center justify-between">
            <div class="flex items-start">
                <div class="mr-4 flex h-16 w-16 items-start justify-center rounded-full">
                    <img src="{{$logoPath}}" alt="{{$logoPath}}" />
                </div>
                <div>
                    <h1 class="text-xl font-bold">LAKSIRI INTERNATIONAL FREIGHT FORWARDERS (PVT) LTD.</h1>
                    <div>
                        <p class="text-lg font-bold">U.P.B. Warehouse</p>
                        <div class="mt-1 grid grid-cols-3 gap-4 text-sm font-medium">
                            <div>
                                <p>66, NEW NUGE ROAD, PELIYAGODA</p>
                            </div>
                            <div>
                                <p>TEL: 2917730-1, 2917890-92</p>
                                <p>FAX: 2917732</p>
                            </div>
                            <div>
                                <p>lakbond@laksirigroup.com</p>
                                <p>V.A.T. Reg. No. : 114162671-7000</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Info Bar -->
    <div class="px-12">
        <div class="flex items-center justify-between space-x-8">
            <div class="flex items-center">
                <span class="text-neutral mr-2 font-medium">Clearing Time:</span>
                <span class="text-neutral-500">{{ $data['clearing_time'] }}</span>
            </div>
            <div class="flex items-center">
                <span class="text-neutral mr-2 font-medium">Date:</span>
                <span class="text-neutral-500">{{ $data['date'] }}</span>
            </div>
            <div class="flex items-center">
                <span class="text-neutral mr-2 font-medium">Serial No:</span>
                <span class="text-neutral-500">IN00708920</span>
            </div>
        </div>
    </div>

    <!-- Invoice Title -->
    <div class="py-2 text-center">
        <h2 class="text-lg font-bold tracking-wide underline">U.P.B. WAREHOUSE INVOICE FOR SEA CARGO</h2>
    </div>

    <!-- Invoice Details -->
    <div class="p-6">
        <!-- Customer & Cargo Information -->
        <div class="mb-3 grid grid-cols-2 gap-8 text-sm">
            <div class="space-y-2">
                <div class="flex">
                    <span class="w-40 font-semibold">Name of Consignee:</span>
                    <span class="text-neutral-500">{{$data['hbl']['consignee_name']}}</span>
                </div>
                <div class="flex">
                    <span class="w-40 font-semibold">Passport/I.D. Card No:</span>
                    <span class="text-neutral-500">{{$data['hbl']['nic']}}</span>
                </div>
                <div class="flex">
                    <span class="w-40 font-semibold">Agent:</span>
                    <span class="text-neutral-500">{{ $data['hbl']['branch']['name'] }}</span>
                </div>
                <div class="flex">
                    <span class="w-32 font-semibold">Total Packages:</span>
                    <span class="text-neutral-500">{{ count($data['hbl']['packages']) }}</span>
                </div>
                <div class="flex">
                    <span class="w-32 font-semibold">Remaining Packages:</span>
                    <span class="text-neutral-500">{{ $data['remaining_packages'] ?? (count($data['hbl']['packages']) - ($data['collected_packages'] ?? 0)) }}</span>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex">
                    <span class="w-32 font-semibold">B/L No:</span>
                    <span class="text-neutral-500">{{ $data['vessel']['bl_number'] ?? ''}}</span>
                </div>
                <div class="flex">
                    <span class="w-32 font-semibold">Vessel:</span>
                    <span class="text-neutral-500">{{ $data['vessel']['vessel_name'] ?? '' }}</span>
                </div>


                <div class="flex">
                    <span class="w-32 font-semibold">Bond Storage No:</span>
                    @if($data['bond_storage_numbers'])
                        <span class="text-neutral-500">{{ implode(', ', $data['bond_storage_numbers']) }}</span>
                    @endif
                </div>
                <div class="flex">
                    <span class="w-32 font-semibold">Volume:</span>
                    <span class="text-neutral-500">{{ $data['grand_volume'] }}</span>
                </div>
            </div>
        </div>

        <!-- Charges Table -->
        <div class="mb-6">
            <div class="overflow-hidden rounded-lg border border-gray-200">
                <table class="w-full">
                    <thead>
                    <tr class="bg-blue-900 text-white">
                        <th class="px-6 py-2 text-left font-bold">DESCRIPTION</th>
                        <th class="px-6 py-2 text-right font-bold">RATE</th>
                        <th class="px-6 py-2 text-right font-bold">AMOUNT</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm">
                    <tr>
                        <td class="px-6 py-1 font-medium">Sri Lanka Port Charges - Sea Cargo</td>
                        <td class="px-6 py-1 text-right">
                            <span class="text-neutral-500">{{ number_format($data['charges']['port_charge']['rate'],2) }}</span>
                        </td>
                        <td class="px-6 py-1 text-right font-medium text-neutral-500">{{ number_format($data['charges']['port_charge']['amount'],2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-1 font-medium"></td>
                        <td class="px-6 py-1 text-right">
                            <span class="text-neutral-500">0.00</span>
                        </td>
                        <td class="px-6 py-1 text-right font-medium text-neutral-500">0.00</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-1 font-medium">
                            Handling Charges
                            <div class="text-neutral text-sm">( Per Package )</div>
                        </td>
                        <td class="px-6 py-1 text-right">
                            <span class="text-neutral-500">{{ number_format($data['charges']['handling_charge']['rate'],2) }}</span>
                        </td>
                        <td class="px-6 py-1 text-right font-medium text-neutral-500">{{ number_format($data['charges']['handling_charge']['amount'],2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-1 font-medium">
                            Bond Storage Charges
                            <div class="text-neutral text-sm">( Per Cubic Foot )</div>
                        </td>
                        <td class="px-6 py-1 text-right">
                            <span class="text-neutral-500">{{ number_format($data['charges']['storage_charge']['rate'],2) }}</span>
                        </td>
                        <td class="px-6 py-1 text-right font-medium text-neutral-500">{{ number_format($data['charges']['storage_charge']['amount'],2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-1 font-medium">
                            Demurrage Charges
                            <div class="text-neutral text-sm">( Per Cubic Foot )</div>
                        </td>
                        <td class="px-6 py-1 text-right">
                            <div class="space-y-1">
                                <div class="text-neutral-500">9.50</div>
                                <div class="text-neutral-500">10.00</div>
                            </div>
                        </td>
                        <td class="px-6 py-1 text-right text-neutral-500">{{ number_format($data['charges']['dmg_charge']['amount'],2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-1 text-xl font-bold">TOTAL</td>
                        <td class="px-6 py-1"></td>
                        <td class="px-6 py-1 text-right text-xl font-bold text-neutral-500">{{ number_format($data['charges']['total'],2)}}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="mb-3 grid grid-cols-2 gap-8">
            <div class="rounded-lg bg-gray-50 px-4 py-2">
                @if (!empty($data['taxes']))
                    <p class="text-xs font-medium text-gray-800">
                        Following taxes are included in your charges:
                    </p>
                    <ul class="list-disc list-inside text-xs text-gray-700">
                        @foreach ($data['taxes'] as $tax)
                            <li>
                                <span>{{ $tax['name'] }}</span> {{ $tax['rate'] }}%
                            </li>
                        @endforeach
                    </ul>
                @endif
                <p class="text-sm font-bold text-neutral-500 mt-2">{{ $data['total_in_word'] }}</p>
            </div>

            <div>
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">D/O Charges:</span>
                        <span class="px-6 font-bold">{{ number_format($data['charges']['do_charge'],2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="font-medium">Stamp Duty:</span>
                        <span class="px-6 font-bold">{{ number_format($data['charges']['stamp_charge'],2) }}</span>
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold">G/Total:</span>
                            <span class="text-2xl font-bold px-6">{{ number_format
                                (
                                    (
                                       $data['charges']['total']+
                                        $data['charges']['do_charge']+
                                        $data['charges']['stamp_charge']
                                    ),2
                                )
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="mb-3 grid grid-cols-2 gap-8 px-4">
            <div>
                <p class="mb-6 text-sm">Cashier - Amount Received</p>
                <div class="mb-3 border-b-2 border-gray-300 pb-4"></div>
                <p class="text-neutral text-sm font-medium">Signature</p>
            </div>
            <div>
                <p class="mb-6">&nbsp</p>
                <div class="mb-3 border-b-2 border-gray-300 pb-4"></div>
                <p class="text-neutral text-right text-sm font-medium">Signature</p>
            </div>
        </div>

        <!-- Footer Note -->
        <div>
            <p class="text-center text-xs font-bold">
                NOTE: WE DO NOT TAKE ANY RESPONSIBILITY FOR ANY LOSS OR DAMAGE<br />
                TO THE GOODS, AFTER CLEARING THE CARGO.
            </p>
        </div>
    </div>
</div>

<style>
    @page {
        size: A4;
    }
</style>
</body>
</html>
