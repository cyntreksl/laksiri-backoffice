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
<table  class="table-row" >
	<tbody>
		<tr>
			<td width="50%">{{ $token->hbl->hbl_number }}</td>
            <td width="50%" style="text-align: right">
                @php
                    $barcode = new Milon\Barcode\DNS1D();
                    echo '<img src="data:image/png;base64,' . $barcode->getBarcodePNG($token->reference, 'C39+', 1,33) . '" alt="barcode" />';
                @endphp
            </td>
		</tr>
	</tbody>
</table>

<table>
    <tbody>
    <tr>
        <td width="50%" >
            @php
                $qrcode = new Milon\Barcode\DNS2D();
                echo '<img src="data:image/png;base64,' . $qrcode->getBarcodePNG($token->hbl->hbl_number, 'QRCODE', 5, 5) . '" alt="barcode" />';
            @endphp
        </td>
        <td width="50%" >
            <h1 style="font-size: 80px">{{ $token->token }}</h1>
        </td>
    </tr>
    </tbody>
</table>

<table  class="table-row" >
    <tbody>
    <tr>
        <td width="50%">Packages: {{$token->package_count}}</td>
        <td width="50%">Created At: {{$token->created_at->format('Y-m-d H:i:s')}}</td>
    </tr>
    </tbody>
</table>
@endsection

