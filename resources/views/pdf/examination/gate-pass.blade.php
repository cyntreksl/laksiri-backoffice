<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargo Release Document - Sea Cargo</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="min-h-screen p-4">
<div class="mx-auto max-w-4xl overflow-hidden text-blue-900">
    <!-- Header -->
    <div class="p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-start">
                <div class="mr-4 flex h-16 w-16 items-start justify-center rounded-full">
                    <img src="{{$logoPath}}" alt="{{$logoPath}}" />
                </div>
                <div>
                    <div>
                        <h1 class="text-2xl font-bold">LAKSIRI INTERNATIONAL FREIGHT FORWARDERS (PVT) LTD.</h1>
                        <p class="font-medium">Cargo Release Document</p>
                        <p>V.A.T. Reg. No: 114162671-7000</p>
                    </div>
                    <!-- Office Information -->
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <h3 class="pb-2 font-bold">Head Office</h3>
                            <div class="space-y-1 text-xs">
                                <p class="font-medium">31, ST. ANTHONY'S MAWATHA,</p>
                                <p class="font-medium">COLOMBO 03, SRI LANKA.</p>
                                <p><span class="font-semibold">TEL:</span> 2575576, 2574180,</p>
                                <p>2577728, 2514247, 2301557</p>
                                <p><span class="font-semibold">Attn:</span> Laksara <span class="font-semibold">Fax:</span> 2577277</p>
                                <p><span class="font-semibold">Email:</span> lakari@laksirigroup.com</p>
                            </div>
                        </div>

                        <div>
                            <h3 class="pb-2 font-bold">Nintavur Branch</h3>
                            <div class="space-y-1 text-xs">
                                <p class="font-medium">U.P.B. WAREHOUSE,</p>
                                <p class="font-medium">101/8, MAIN STREET,</p>
                                <p class="font-medium">ADDAPPALLAM, NINTAVUR.</p>
                                <p><span class="font-semibold">TEL:</span> 067 2051050, 067 2051051</p>
                                <p><span class="font-semibold">Fax:</span> 067 2051052</p>
                                <p><span class="font-semibold">Email:</span> upbeast@laksirigroup.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Title -->
    <div class="py-1 text-center">
        <h2 class="text-xl font-bold tracking-wide">SEA CARGO RELEASE DOCUMENT</h2>
    </div>

    <!-- Document Details -->
    <div class="px-6 py-2">
        <!-- Reference Information -->
        <div class="mb-6 grid grid-cols-3 gap-6 text-sm">
            <div class="rounded-lg bg-gray-50 p-4">
                <div class="flex items-center justify-between">
                    <span class="font-semibold">Vehicle No:</span>
                    <span class="font-bold text-neutral-500">CM-3572</span>
                </div>
            </div>
            <div class="rounded-lg bg-gray-50 p-4">
                <div class="flex items-center justify-between">
                    <span class="font-semibold">Gate Pass:</span>
                    <span class="font-bold text-neutral-500">{{$data['token']}}</span>
                </div>
            </div>
            <div class="rounded-lg bg-gray-50 p-4">
                <div class="flex items-center justify-between">
                    <span class="font-semibold">No:</span>
                    <span class="font-bold text-neutral-500">IN00708920</span>
                </div>
            </div>
        </div>

        <!-- Date and Destination -->
        <div class="mb-6 grid grid-cols-2 gap-6 text-sm">
            <div class="rounded-lg bg-gray-50 p-4">
                <div class="mb-2 flex items-center justify-between">
                    <span class="font-semibold">Destination Date:</span>
                    <span class="font-bold text-neutral-500">{{ $data['date'] }}</span>
                </div>
            </div>
            <div class="rounded-lg bg-gray-50 p-4">
                <div class="mb-2 flex items-center justify-between">
                    <span class="font-semibold">Time:</span>
                    <span class="font-bold text-neutral-500">{{ $data['time'] }}</span>
                </div>
            </div>
        </div>

        <!-- Cargo Information -->
        <div class="mb-6 rounded-lg border border-gray-200 text-sm">
            <div class="space-y-4 px-6 py-3">
                <div class="grid grid-cols-2 gap-6">
                    <div class="flex">
                        <span class="w-40 font-semibold">B/L No:</span>
                        <span class="font-medium text-neutral-500">{{ $data['vessel']['bl_number'] ?? '' }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-40 font-semibold">No. of Packages:</span>
                        <span class="font-medium text-neutral-500">{{ count($data['hbl']['packages']) }}</span>
                    </div>
                </div>

                <div class="pt-4">
                    <div class="flex">
                        <span class="w-40 font-semibold">Description of goods:</span>
                        <span class="font-medium text-neutral-500">
                            @foreach($data['hbl']['packages'] as $package)
                                {{ $package['package_type'] }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 pt-4">
                    <div class="flex">
                        <span class="w-40 font-semibold">Passport/I.D. Card No:</span>
                        <span class="font-medium text-neutral-500">
                            {{$data['hbl']['nic']}}
                        </span>
                    </div>
                    <div class="flex">
                        <span class="w-40 font-semibold">Bond Storage No:</span>
                        <span class="font-medium text-neutral-500">05/1321 -1325</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="flex">
                        <span class="w-40 font-semibold">Vessel:</span>
                        <span class="font-medium text-neutral-500">
                            {{ $data['vessel']['vessel_name'] ?? ''}}
                        </span>
                    </div>
                    <div class="flex">
                        <span class="w-40 font-semibold">Delivered from:</span>
                        <span class="font-medium text-neutral-500"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Authorization Section -->
        <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-6">
            <p class="mb-3 text-gray-700">The above goods may be permitted to be removed</p>

            <div class="grid grid-cols-3 gap-4 text-sm">
                <div>
                    <div class="flex">
                        <span class="text-neutral w-20 font-semibold">BY:</span>
                        <span class="font-bold text-neutral-500">{{ $data['by'] }}</span>
                    </div>
                </div>
                <div>
                    <div class="flex">
                        <span class="text-neutral w-20 font-semibold">Date:</span>
                        <span class="font-bold text-neutral-500">{{ \Carbon\Carbon::now()->toDateString() }}</span>
                    </div>
                </div>
                <div>
                    <div class="flex">
                        <span class="text-neutral w-20 font-semibold">Time:</span>
                        <span class="font-bold text-neutral-500">{{ \Carbon\Carbon::now()->toTimeString() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reference Details -->
        <div class="p-3 text-sm">
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-lg p-3">
                    <p class="mb-1 font-semibold">Reference No:</p>
                    <p class="font-bold text-neutral-500">{{$data['hbl']['reference']}}</p>
                </div>
                <div class="rounded-lg p-3">
                    <p class="mb-1 font-semibold">Serial No:</p>
                    <p class="font-bold text-neutral-500">{{$data['serial']}}</p>
                </div>
                <div class="rounded-lg p-3">
                    <p class="mb-1 font-semibold">User ID:</p>
                    <p class="font-bold text-neutral-500">{{$data['userId']}}</p>
                </div>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="p-2">
            <div class="text-center text-sm">
                <div class="mb-4 border-b-2 border-dashed border-gray-300 pb-6"></div>
                <p class="font-bold">Officer</p>
            </div>
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
