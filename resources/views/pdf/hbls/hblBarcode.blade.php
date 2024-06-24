@extends('pdf.main')

@section('pdf-content')

<style>
* {

    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

}
.table-row {

    border-bottom: 2px solid black;
    border-collapse: collapse;
}

</style>

@php

$count=count($hbl->packages);

@endphp

@foreach ($hbl->packages as $package)
    

<table  class="table-row" >
	<tbody>
		<tr>
			<td width="40%" >{{ $hbl->created_at }}</td>
			<td width="40%">{{ $hbl->reference }}</td>
            <td width="20%">laksiriwik@gmail.com</td>
		</tr>
	</tbody>
</table>

<table  class="table-row"  >
	<tbody>
		<tr  >
			<td width="80%" style="text-align: center;font-size: 20px;">{{$hbl?->cargo_type}} / {{$hbl?->hbl_type}} </td>
			<td width="20%"  style="text-align: right;" >
                PACKAGES<br>
                <label style="font-size: 40px;" >{{ $loop->iteration }}/{{$count}}</label>
             
            </td>
		</tr>
	</tbody>
</table>


<table  class="table-row"  >
	<tbody>
		<tr>
			<td style="text-align: left;" >From:<br>
                {{ $hbl->hbl_name}}<br>{{ $hbl->address }}<br>{{ $hbl->contact_number }}<br>
            </td>
		</tr>
	</tbody>
</table>


<table  class="table-row"   >
	<tbody>
		<tr>
            <td style="text-align: left;" >To:<br>
                {{ $hbl->consignee_name }}<br>{{ $hbl->consignee_address }}<br>{{ $hbl->consignee_contact }}<br>
               
            </td>
		</tr>
	</tbody>
</table>


<table  class="table-row" >
	<tbody>
		<tr>
			<td>ORIGIN:
				<table>
					<tr>
						<td><label style="font-size: 25px;" >{{ $hbl->warehouse }}</label></td>
					<tr>
				</table>
                
            </td>
			<td>DESTINATION:
				<table>
					<tr>
						<td>  <label style="font-size: 25px;" ></label></td>
					<tr>
				</table>
            </td>
		</tr>
	</tbody>
</table>

<table  class="table-row" >
	<tbody>
		<tr>
			<td>WEIGHT:
                <br>
                <label  style="font-size: 15px" >{{$package?->weight}}</label>
            </td>
			<td>VOLUME:<br>
                <label  style="font-size: 15px" >{{$package?->volume}}</label>
            </td>

		</tr>
	</tbody>
</table>


<table  class="" >
	<tbody>
		<tr>
			<td>HBL:
				<table>
					<tr>
						<td><label  style="font-size: 25px" >
							{{ $hbl->hbl }}<label></td>
					<tr>
				</table>
		
				</td>
			<td>PID: 
				<table>
					<tr>
						<td><label  style="font-size: 20px" >
							{{$package?->package_type}}<label></td>
					<tr>
				</table>
		</tr>
	</tbody>
</table>


<table  class="table-row" >
	<tbody>
		<tr>
			<td>MHBL:<br>
                <label style="font-size: 15px" >fsdfsdfsdf</label>
               
            </td>
		</tr>
	</tbody>
</table>

<table   >
	<tbody>
		<tr>
			<td style="text-align: center;" >
                @php
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
echo '<img   style="height: 100px;"        width="80%" src="data:image/png;base64,' . base64_encode($generator->getBarcode($hbl->reference, $generator::TYPE_CODE_128, 3, 80)) . '">';
                @endphp
            </td>
		</tr>
	</tbody>
</table>

@endforeach
@endsection

