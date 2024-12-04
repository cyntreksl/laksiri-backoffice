@extends('pdf.main')

@section('pdf-content')
    <table class="table-row">
        <tbody>
        <tr style="vertical-align: top;">
            <td style="padding-top: 11%; width: 50%;">
                <table class="table-row">
                    <tr>
                        <td colspan="2" ></td>
                        <td colspan="2" >{{ $data['clearing_time'] }}</td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td></td>
                        <td>{{ $data['date'] }}</td>
                        <td></td>
                        <td>{{ $data['clearing_time'] }}</td>
                    </tr>
                </table>

                <table class="table-row" style="padding-top: 15%;">
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">W.A.G.N.Gunasinghe</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">00453</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">Vessel</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">passport</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">no pks</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">bo sto</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">agent</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">volume</td>
                    </tr>
                </table>

                <table class="table-row" style="padding-top: 10%;">
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;">600.00</td>
                        <td style="width: 20%;">00.00</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;">00.00</td>
                        <td style="width: 20%;">00.00</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;">00.00</td>
                        <td style="width: 20%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;">270.00</td>
                        <td style="width: 20%;">00.00</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;"></td>
                        <td style="width: 20%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;">9.50 <br/> 10.50</td>
                        <td style="width: 20%;">4310.00</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;"></td>
                        <td style="width: 20%; font-weight: bold;">4310.00</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;"></td>
                        <td style="width: 20%;">2500.00</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;"></td>
                        <td style="width: 20%;">00.00</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;"></td>
                        <td style="width: 20%; font-weight: bold;">6810.16</td>
                    </tr>
                </table>

                <table class="table-row" style="padding-top: 8%;">
                    <tr>
                        <td>Six thousand eight hundred</td>
                    </tr>
                </table>
            </td>
            <td style="padding-top: 11%; width: 50%;">
                <table class="table-row" border="1">
                    <tr>
                        <td></td>
                        <td>CM-3572</td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td></td>
                        <td>003</td>
                    </tr>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
@endsection
