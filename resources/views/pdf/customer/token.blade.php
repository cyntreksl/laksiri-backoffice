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
			<td width="50%">{{ $token->reference }}</td>
            <td width="50%" style="text-align: right">laksiriwik@gmail.com</td>
		</tr>
	</tbody>
</table>

<table>
    <tbody>
    <tr>
        <td width="40%" >
            <h1 style="font-size: 80px">{{ $token->token }}</h1>
        </td>
    </tr>
    </tbody>
</table>
@endsection

