@extends('pdf.main')

@section('pdf-header')
    @parent
    @include('pdf.hbls.partials.headerInvoice')
@endsection

@section('pdf-content')
    @include('pdf.hbls.partials.header')
@php
   $branch = $hbl->branch ?? App\Models\Branch::find($hbl?->branch_id);
   // Calculate SL Portal Total (Destination II + Destination I only if not paid)
   $destinationCharge = $hbl->destinationCharge;
   $slPortalTotal = 0;
   if ($destinationCharge) {
       $dest2Charges = ($destinationCharge->destination_2_total ?? 0) + ($destinationCharge->destination_2_tax ?? 0);
       if ($hbl->branch->is_prepaid ?? false) {
           $slPortalTotal = $dest2Charges;
       } else {
           $slPortalTotal = $dest2Charges;
           if (!($hbl->is_destination_charges_paid ?? false)) {
               $slPortalTotal += ($destinationCharge->destination_1_total ?? 0) + ($destinationCharge->destination_1_tax ?? 0);
           }
       }
   }
@endphp

<style>
    .footer {
        position: fixed;
        bottom: 0;
        width: 100%;
        text-align: center;
        font-size: 12px;
        font-family: 'Times New Roman', serif;
        left: 0;
    }

    .footer {
        position: fixed;
        bottom: 0;
        width: 100%;
        font-size: 12px;
        font-family: 'Times New Roman', serif;
        left: 0;
        display: flex; /* Use flexbox for layout */
        justify-content: space-between; /* Distribute space between elements */
        align-items: center; /* Vertically center items */
        padding: 0 20px; /* Add some padding to the sides */
    }

    .footer-text {
        text-align: left;
    }
</style>
<table>
    <tbody>
        <tr>
            <td style="text-align: center;">
                <b>CASH RECEIPT</b>
            </td>
        </tr>
    </tbody>
</table>

<table>
    <tr>
        <td>
            <table style="border-collapse: collapse;" >

                <tr>
                    <td colspan="2">Received From:</td>
                </tr>
                <tr>
                    <td colspan="2">{{$hbl?->hbl_name}} <br>{{$hbl?->address}}</td>
                </tr>

            </table>
        </td>
        <td style="width: 5%;">&nbsp;&nbsp;</td>
        <td
            >
            <table class="head-table">

                <tr>
                    <td  style="width: 15%;" >C.R.No</td>
                    <td style="width: 2%;" >:</td>
                    <td>{{ $hbl?->hbl_number }}</td>
                </tr>
                <tr>
                    <td>Time:</td>
                    <td>:</td>
                    <td>{{  Carbon\Carbon::parse($hbl?->created_at)->format('Y-m-d') }}</td>
                </tr>
                <tr>
                    <td>Date</td>
                    <td>:</td>
                    <td>{{ Carbon\Carbon::parse($hbl?->created_at)->format('h:i A') }}</td>
                </tr>
                <tr>
                    <td>Salesman:</td>
                    <td>:</td>
                    <td>{{ $salesman ? $salesman->name : '-' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<hr style="border:1px solid silver;padding: 0px;">

<table class="data-table" >
    <tr>
        <td>S.No</td>
        <td>Description</td>
        <td>Cargo Type</td>
        <td>Amount</td>
    </tr  >

    <tr style="border-top: 2px solid black;" >
<td colspan="4" ><br></td>

    </tr>
    <tr   >
        <td   >1</td>
        <td>{{$hbl?->hbl_number}}</td>
        <td>{{$hbl?->cargo_type}} \ {{$hbl?->hbl_type}}</td>
        <td>{{number_format($hbl?->grand_total ?? 0, 2) }} {{$branch->currency_symbol ?? 'LKR'}}</td>

    </tr>

</table>

<div style="height: 300px;">

</div>

<hr style="padding: 0px;">

<table class="footer-table" >
	<tbody>
		<tr>
			<td colspan="3" rowspan="3">
Terms and conditions will be provided on request</td>
			<td  style="width: 15%;" >	Total</td>
			<td style="width: 2%;" >:</td>
			<td>{{number_format($hbl?->grand_total ?? 0, 2) }}  {{$branch->currency_symbol ?? 'LKR'}}</td>
		</tr>
		<tr>
			<td>Less Discount</td>
			<td>:</td>
			<td>{{number_format($hbl?->discount ?? 0, 2) }}</td>
		</tr>
		<tr>
			<td>Net Amount</td>
			<td>:</td>
			<td>{{number_format(($hbl?->grand_total ?? 0) - ($hbl?->discount ?? 0), 2) }}  {{$branch->currency_symbol ?? 'LKR'}}</td>
		</tr>
		<tr>
            @php
            $netAmount = ($hbl?->grand_total ?? 0) - ($hbl?->discount ?? 0);
            $amountword = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
        @endphp
			<td colspan="6">Amount in words : {{ $amountword ->format($netAmount)}}  only</td>
		</tr>
		<tr>
			<td colspan="3">Remarks / Destination
                <br>
                <div style="height: 30px" ></div>
            </td>
			<td colspan="3" rowspan="5"  style=" vertical-align: top;">
           For  {{$invoice_header_title}}
                <div style="height: 50px" ></div>
            </td>
		</tr>
		<tr>
			<td colspan="3">Received As below

            </td>
		</tr>
		<tr>
			<td   style="width: 15%;" >Cash</td>
			<td style="width: 2%;" >:</td>
			<td></td>
		</tr>
		<tr>
			<td>Cheque No. / Date</td>
			<td>:</td>
			<td></td>
		</tr>
		<tr>
			<td>Credit Card</td>
			<td>:</td>
			<td></td>
		</tr>
		<tr>
			<td>Total</td>
			<td>:</td>
			<td>{{number_format(($hbl?->grand_total ?? 0) - ($hbl?->discount ?? 0), 2) }}  {{$branch->currency_symbol ?? 'LKR'}}</td>
			<td colspan="3"  style="text-align: right;">Authorized Signatory</td>
		</tr>
	</tbody>
</table>

@endsection
