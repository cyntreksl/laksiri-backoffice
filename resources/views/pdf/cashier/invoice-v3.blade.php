<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Invoice - Sea Cargo</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="text-gray-800">

{{-- CASHIER'S COPY --}}
<div style="position: absolute; top: 2cm; left: 9cm;">CASHIER'S COPY</div>

{{-- Clearing Time --}}
<div style="position: absolute; top: 5cm; left: 4.5cm;"><span>Clearing Time : </span>
    <span>{{ \Carbon\Carbon::now('Asia/Colombo')->toTimeString() }}</span>
</div>

{{-- Date --}}
<div style="position: absolute; top: 5.5cm; left: 3.5cm;">
    {{ \Carbon\Carbon::today()->toDateString()}}
</div>

{{-- Serial Number --}}
<div style="position: absolute; top: 5.5cm; left: 9cm;">{{ $data['hbl']['hbl_number'] ?? '' }}</div>

{{-- B/L No --}}
<div>{{ $data['vessel']['bl_number'] ?? ''}}</div>

{{-- Name of Consignee --}}
<div>{{$data['hbl']['consignee_name']}}</div>

{{-- Vessel --}}
<div>{{ $data['vessel']['vessel_name'] ?? '' }}</div>

{{-- Passport / I.D. Card No --}}
<div>{{$data['hbl']['nic']}}</div>

{{-- >No. of Packages --}}
<div>{{ count($data['hbl']['packages']) }}</div>

{{-- Bond Storage No. --}}
<div>@if(!empty($data['bond_storage_numbers'])){{ implode(', ', $data['bond_storage_numbers']) }}@else N/A @endif</div>

{{-- Agent --}}
<div>{{ $data['hbl']['branch']['name'] }}</div>

{{-- Volume --}}
<div>{{ $data['grand_volume'] }}</div>

{{-- Table --}}
{{-- Sri Lanka Port Charges - Sea Cargo --}}
<div>{{ number_format($data['charges']['port_charge']['rate'],2) }}</div>
<div>{{ number_format($data['charges']['port_charge']['amount'],2) }}</div>

<div>0.00</div>
<div>0.00</div>

{{-- Handling Charges ( Per Package ) --}}
<div>{{ number_format($data['charges']['handling_charge']['rate'],2) }}</div>
<div>{{ number_format($data['charges']['handling_charge']['amount'],2) }}</div>

{{-- Bond Storage Charges ( Per Cubic Foot ) --}}
<div>{{ number_format($data['charges']['storage_charge']['rate'],2) }}</div>
<div>{{ number_format($data['charges']['storage_charge']['amount'],2) }}</div>

{{-- Demurrage Charges ( Per Cubic Foot ) --}}
<div>
    9.50<br />
    10.00
</div>
<div>{{ number_format($data['charges']['dmg_charge']['amount'],2) }}</div>

{{-- TOTAL --}}
<div>{{ number_format($data['charges']['total'],2)}}</div>


<!-- Payment Summary -->
<div><span>D/O Charges. </span><span>{{ number_format($data['charges']['do_charge'],2) }}</span></div>

<div><span>Stamp Duty </span><span>{{ number_format($data['charges']['stamp_charge'],2) }}</span></div>

<div class="font-bold"><span class="">G/Total : </span><span>{{ number_format($data['charges']['total']+$data['charges']['do_charge']+$data['charges']['stamp_charge'],2) }}</span></div>

@if (!empty($data['taxes']))
    <div>VAT 18% included in the charges</div><br />
    <div>{{ strtoupper($data['total_in_word']) }}</div>
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
