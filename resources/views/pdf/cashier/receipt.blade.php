<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $data['hbl']['reference'] }}</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @page {
            size: 80mm 297mm;
            /* Set width to 80mm, height can be variable or fixed max A4 length for roll paper simulation */
            margin: 0;
        }

        body {
            width: 80mm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            color: black;
            background: white;
            margin: 0;
            padding: 5px;
        }

        .text-xxs {
            font-size: 8px;
        }
    </style>
</head>

<body>
    <div class="flex flex-col items-center text-center">
        @if($logoPath)
            <img src="{{$logoPath}}" alt="Logo" class="w-16 mb-2" />
        @endif
        <h1 class="font-bold text-sm">LAKSIRI INTERNATIONAL</h1>
        <h2 class="font-bold text-xs">FREIGHT FORWARDERS (PVT) LTD.</h2>
        <div class="text-xxs mt-1">
            <p>66, New Nuge Road, Peliyagoda</p>
            <p>Tel: 2917730-1, 2917890-92</p>
            <p>VAT Reg: 114162671-7000</p>
        </div>
    </div>

    <div class="border-b border-black border-dashed my-2"></div>

    <div class="text-xs">
        <div class="flex justify-between">
            <span>Date:</span>
            <span>{{ $data['date'] }}</span>
        </div>
        <div class="flex justify-between">
            <span>HBL:</span>
            <span class="font-bold">{{ $data['hbl']['hbl_number'] ?? $data['hbl']['reference'] }}</span>
        </div>
        <div class="flex justify-between">
            <span>Cashier:</span>
            <span>{{ $data['by'] }}</span>
        </div>
    </div>

    <div class="border-b border-black border-dashed my-2"></div>

    <div class="text-xs font-bold mb-1">Items</div>
    <div class="w-full text-xs">
        <div class="flex justify-between font-bold border-b border-black mb-1 pb-1">
            <span>Desc</span>
            <span>Amount</span>
        </div>

        <div class="flex justify-between mb-1">
            <span>Port Charges</span>
            <span>{{ number_format($data['charges']['port_charge']['amount'], 2) }}</span>
        </div>
        <div class="flex justify-between mb-1">
            <span>Handling Charges</span>
            <span>{{ number_format($data['charges']['handling_charge']['amount'], 2) }}</span>
        </div>
        <div class="flex justify-between mb-1">
            <span>Storage Charges</span>
            <span>{{ number_format($data['charges']['storage_charge']['amount'], 2) }}</span>
        </div>
        <div class="flex justify-between mb-1">
            <span>Demurrage Charges</span>
            <span>{{ number_format($data['charges']['dmg_charge']['amount'], 2) }}</span>
        </div>
        <div class="flex justify-between mb-1">
            <span>D/O Charges</span>
            <span>{{ number_format($data['charges']['do_charge'], 2) }}</span>
        </div>
        <div class="flex justify-between mb-1">
            <span>Stamp Duty</span>
            <span>{{ number_format($data['charges']['stamp_charge'], 2) }}</span>
        </div>

        <div class="border-t border-black border-dashed my-1"></div>

        <div class="flex justify-between font-bold text-sm">
            <span>TOTAL</span>
            <span>{{ number_format($data['charges']['total'] + $data['charges']['do_charge'] + $data['charges']['stamp_charge'], 2) }}</span>
        </div>
    </div>

    <div class="border-b border-black border-dashed my-2"></div>

    <div class="text-center text-xxs mt-2">
        <p>Thank you for your business!</p>
        <p>Start Date: {{ $data['hbl']['created_at'] }}</p>
    </div>
</body>

</html>