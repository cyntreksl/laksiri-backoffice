<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Invoice - Sea Cargo</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="text-gray-800 text-xs">

{{-- CASHIER'S COPY --}}
<div style="position: absolute; top: 1.2cm; left: 8cm;" class="text-lg">CASHIER'S COPY</div>

{{-- Clearing Time --}}
<div style="position: absolute; top: 4.2cm; left: 4.2cm;"><span>Clearing Time : </span>
    <span>{{ \Carbon\Carbon::now('Asia/Colombo')->toTimeString() }}</span>
</div>

{{-- Date --}}
<div style="position: absolute; top: 4.9cm; left: 2cm;">
    {{ \Carbon\Carbon::today()->toDateString()}}
</div>

{{-- Serial Number --}}
<div style="position: absolute; top: 4.9cm; left: 8cm;">{{ $data['hbl']['hbl_number'] ?? '' }}</div>

{{-- Name of Consignee --}}
<div style="position: absolute; top: 7.4cm; left: 5cm;">{{$data['hbl']['consignee_name']}}</div>

{{-- B/L No --}}
<div style="position: absolute; top: 8.3cm; left: 5cm;">{{ $data['vessel']['bl_number'] ?? ''}}</div>

{{-- Vessel --}}
<div style="position: absolute; top: 9.2cm; left: 5cm;">{{ $data['vessel']['vessel_name'] ?? '' }}</div>

{{-- Passport / I.D. Card No --}}
<div style="position: absolute; top: 10.3cm; left: 5cm;">{{$data['hbl']['nic']}}</div>

{{-- >No. of Packages --}}
<div style="position: absolute; top: 11.1cm; left: 5cm;">{{ count($data['hbl']['packages']) }}</div>

{{-- Bond Storage No. --}}
<div style="position: absolute; top: 11.8cm; left: 5cm;">@if(!empty($data['bond_storage_numbers'])){{ implode(', ', $data['bond_storage_numbers']) }}@else N/A @endif</div>

{{-- Agent --}}
<div style="position: absolute; top: 12.5cm; left: 5cm;">{{ $data['hbl']['branch']['name'] }}</div>

{{-- Volume --}}
<div style="position: absolute; top: 13.5cm; left: 5cm;">{{ $data['grand_volume'] }}</div>

{{-- Table --}}
{{-- Sri Lanka Port Charges - Sea Cargo --}}
<div style="position: absolute; top: 15cm; left: 7cm;">{{ number_format($data['charges']['port_charge']['rate'],2) }}</div>
<div style="position: absolute; top: 15cm; left: 9cm;">{{ number_format($data['charges']['port_charge']['amount'],2) }}</div>

<div style="position: absolute; top: 15.9cm; left: 7cm;">0.00</div>
<div style="position: absolute; top: 15.9cm; left: 9cm;">0.00</div>

{{-- Handling Charges ( Per Package ) --}}
<div style="position: absolute; top: 16.5cm; left: 7cm;">{{ number_format($data['charges']['handling_charge']['rate'],2) }}</div>
<div style="position: absolute; top: 16.5cm; left: 9cm;">{{ number_format($data['charges']['handling_charge']['amount'],2) }}</div>

{{-- Bond Storage Charges ( Per Cubic Foot ) --}}
<div style="position: absolute; top: 17.4cm; left: 7cm;">{{ number_format($data['charges']['storage_charge']['rate'],2) }}</div>
<div style="position: absolute; top: 17.4cm; left: 9cm;">{{ number_format($data['charges']['storage_charge']['amount'],2) }}</div>

{{-- Demurrage Charges ( Per Cubic Foot ) --}}
<div style="position: absolute; top: 19.1cm; left: 7cm;">9.50</div>
<div style="position: absolute; top: 19.7cm; left: 7cm;">10.00</div>

<div style="position: absolute; top: 19.4cm; left: 9cm;">{{ number_format($data['charges']['dmg_charge']['amount'],2) }}</div>

{{-- TOTAL --}}
<div style="position: absolute; top: 20cm; left: 9cm;" class="font-bold text-lg">{{ number_format($data['charges']['total'],2)}}</div>


<!-- Payment Summary -->
<div style="position: absolute; top: 21.2cm; left: 4cm;"><span>D/O Charges. </span><span>{{ number_format($data['charges']['do_charge'],2) }}</span></div>

<div style="position: absolute; top: 21.7cm; left: 4cm;"><span>Stamp Duty </span><span>{{ number_format($data['charges']['stamp_charge'],2) }}</span></div>

<div style="position: absolute; top: 22.3cm; left: 4cm;" class="font-bold"><span>G/Total : </span><span>{{ number_format($data['charges']['total']+$data['charges']['do_charge']+$data['charges']['stamp_charge'],2) }}</span></div>

@if (!empty($data['taxes']))
    <div style="position: absolute; top: 24.5cm; left: 1.8cm;">VAT 18% included in the charges</div><br />
    <div style="position: absolute; top: 25.5cm; left: 1.8cm;">{{ strtoupper($data['total_in_word']) }}</div>
@endif

{{-- RIGHT SIDE --}}

<!-- Gate Pass Details -->
{{--No.--}}
<div>{{ $data['hbl']['hbl_number'] ?? '' }}</div>

{{-- Destuff Date --}}
<div>Destuff Date.</div>
<div>
    {{ $data['vessel']['unloading_ended_at'] ? \Carbon\Carbon::parse($data['vessel']['unloading_ended_at'])->format('Y-m-d') : '' }}
</div>

{{--B/L No.--}}
<div>{{ $data['vessel']['bl_number'] ?? ''}}</div>

{{--Description of goods--}}
<div>{{ collect($data['hbl']['packages'])->pluck('package_type')->filter()->implode(', ') }}</div>

{{--No. of Packages--}}
<div>{{ count($data['hbl']['packages']) }}</div>

{{--Passport / I.D. Card No.--}}
<div>{{$data['hbl']['nic']}}</div>

{{--Bond Storage No.--}}
<div>@if(!empty($data['bond_storage_numbers'])){{ implode(', ', $data['bond_storage_numbers']) }}@else N/A @endif</div>

{{--Vessel--}}
<div>{{ $data['vessel']['vessel_name'] ?? '' }}</div>

{{--BY--}}
<div>{{$data['hbl']['consignee_name']}}</div>

{{--Date--}}
<div>{{ \Carbon\Carbon::today()->toDateString()  }}</div>

{{--Time--}}
<div>{{ \Carbon\Carbon::now('Asia/Colombo')->toTimeString() }}</div>

{{--User ID--}}
<div>{{ strtoupper($data['by']) }}</div>
</body>
</html>
