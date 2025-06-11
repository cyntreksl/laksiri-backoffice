<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>{{$container->reference}}</title>
</head>
<body>
<body class="bg-white text-black p-4 font-sans text-sm">
<div class="max-w-3xl mx-auto p-6 rounded-xl space-y-3">
    <!-- Header -->
    <div class="text-center">
        @if($logoPath)
            <div>
                <img style="max-width: 140px; max-height: 80px; margin-right: -100px; float: left;"
                     src="{{ $logoPath }}"
                     alt="app_logo">
            </div>
        @endif
        <div>
            <h1 class="text-xl font-bold uppercase">Laksiri Seva (Pvt) Ltd</h1>
            <div class="text-xs text-gray-500">
                <p>Head Office: 31, St. Anthony's Mawatha, Colombo 03, Sri Lanka</p>
                <p>Tel: 2574180 | Fax: 2571777 / 2472187</p>
                <p>Email: laksirisewa@laksirigroup.com | Web: www.laksirigroup.com</p>
            </div>
        </div>
    </div>

    <!-- Document Info -->
    <div class="grid grid-cols-1 gap-2 text-sm">
        <p>LAK/WH/SEA/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/{{\Illuminate\Support\Carbon::now()->year}}</p>
        <p>CUS/NO:</p>
        <p>{{\Illuminate\Support\Carbon::now()->toDateString()}}</p>
    </div>

    <p class="mt-4">The Director General of Customs,<br>Sri Lanka Customs,<br>Colombo.</p>

    <p>Dear Sir,</p>

    <!-- Container Details -->
    <table class="w-full text-sm font-bold border-b">
        <tr>
            <td class="py-2 w-1/4">CONTAINER NO:</td>
            <td class="py-2 w-1/4">{{$container?->container_type}}</td>
            <td class="py-2 w-1/4">{{$container?->reference}}</td>
            <td class="py-2 w-1/4">FCL CONTAINER</td>
        </tr>
        <tr>
            <td class="py-2">VESSEL NAME & DATE:</td>
            <td class="py-2">{{$container?->vessel_name}}</td>
            <td class="py-2">OF</td>
            <td class="py-2">{{$container?->estimated_time_of_arrival}}</td>
        </tr>
        <tr>
            <td class="py-2">BILL OF LADING NO:</td>
            <td class="py-2" colspan="3">{{$container?->bl_number}}</td>
        </tr>
        <tr>
            <td class="py-2">PORT OF LOADING:</td>
            <td class="py-2" colspan="3">{{$container?->port_of_loading}}</td>
        </tr>
        <tr>
            <td class="py-2">NO OF PACKAGES:</td>
            <td class="py-2" colspan="3">{{$container?->hbl_packages->count()}} PKGS (PERSONAL EFFECTS CARGO)</td>
        </tr>
        <tr>
            <td class="py-2">SHIPPER:</td>
            <td class="py-2" colspan="3">{{$container?->branch?->name}}</td>
        </tr>
        <tr>
            <td class="py-2">TIN NO:</td>
            <td class="py-2" colspan="3">1140195007000</td>
        </tr>
        <tr>
            <td class="py-2">CUSTOM HOUSE AGENT NO:</td>
            <td class="py-2" colspan="3">0176-101</td>
        </tr>
    </table>

    <!-- Request -->
    <p>Please grant permission to transfer the above container from the Colombo Port to the <strong>Laksiri Seva (Pvt) Ltd</strong>, U.P.B. Bonded Warehouse at No.66, New Nuge Road, Peliyagoda.</p>

    <p>This container / said to contain {{$container?->hbl_packages->count()}} packages personal effects <strong>{{$container->port_of_loading ? $container->port_of_loading : '..........................................................'}}</strong> No objection to removal to Laksiri Seva U.P.B. Bonded Warehouse under Customs Supervision.</p>

    <p>Thanking you,</p>

    <div class="flex justify-between items-start gap-6">
        <!-- Signatures -->
        <div class="w-1/2 space-y-2">
            <p class="mb-0">Yours faithfully,<br><strong>LAKSIRI SEVA (PVT) LTD.</strong></p>
            <p class="mb-0">Manager</p>
            <p>{{ auth()->user()->name }}</p>
            <p class="mb-5">Permission granted</p>
            <p class="mb-0">..........................................................</p>
            <p class="text-sm italic">S.D.D.C. (Laksiri Seva)<br>U.P.B. Bonded Warehouse</p>
        </div>

        <!-- Customs Office -->
{{--        <div class="w-1/2 border border-gray-300 p-4 text-center">--}}
{{--            <p class="text-sm font-medium mb-2">NO OBJECTION FOR REMOVAL <br> TO LAKSIRI BOND UNDER CUSTOMS <br> SUPERVISION SUBJECT TO OF SDDC LAKSIRI</p>--}}
{{--            <p class="mb-0">..........................................................</p>--}}
{{--            <p class="text-xs mb-2">SDDC/CO/SC (Baggage Office)</p>--}}
{{--        </div>--}}
    </div>

    <!-- Footer -->
    <div class="text-center text-xs text-gray-500 mt-3 pt-4">
        <p>U.P.B. WAREHOUSE<br>66, NEW NUGE ROAD, PELIYAGODA, SRI LANKA</p>
        <p>TEL: 291772931 | FAX: 2917732</p>
        <p>Email: lakbond@laksirigroup.com</p>
    </div>
</div>
</body>
</body>
</html>
