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
{{--                        <td style="width: 50%;">{{$data['hbl'] }}</td>--}}
                        <td style="width: 50%;">{{$data['hbl']['consignee_name']}}</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">{{ $data['vessel']['bl_number'] ?? ''}}</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">{{ $data['vessel']['vessel_name'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">{{$data['hbl']['nic']}}</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">{{ count($data['hbl']['packages']) }}</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">05/1321 -1325</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">{{ $data['hbl']['branch']['name'] }}</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">{{ $data['grand_volume'] }}</td>
                    </tr>
                </table>

                <table class="table-row" style="padding-top: 10%;">
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;">{{ number_format($data['charges']['port_charge']['rate'],2) }}</td>
                        <td style="width: 20%;">{{ number_format($data['charges']['port_charge']['amount'],2) }}</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;">00.00</td>
                        <td style="width: 20%;">00.00</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;">{{ number_format($data['charges']['handling_charge']['rate'],2) }}</td>
                        <td style="width: 20%;">{{ number_format($data['charges']['handling_charge']['amount'],2) }}</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;">{{ number_format($data['charges']['storage_charge']['rate'],2) }}</td>
                        <td style="width: 20%;">{{ number_format($data['charges']['storage_charge']['amount'],2) }}</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;"></td>
                        <td style="width: 20%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;">9.50 <br/> 10.50</td>
                        <td style="width: 20%;">{{ number_format($data['charges']['dmg_charge']['amount'],2) }}</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;"></td>
                        <td style="width: 20%; font-weight: bold;">{{ number_format($data['charges']['dmg_charge']['amount'],2) }}</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;"></td>
                        <td style="width: 20%;">{{ number_format($data['charges']['do_charge'],2) }}</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;"></td>
                        <td style="width: 20%;">{{ number_format($data['charges']['stamp_charge'],2) }}</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 20%;"></td>
                        <td style="width: 20%; font-weight: bold;">{{ number_format($data['charges']['total'],2) }}</td>
                    </tr>
                </table>

                <table class="table-row" style="padding-top: 8%;">
                    <tr>
                        <td>{{ $data['total_in_word'] }}</td>
                    </tr>
                </table>
            </td>
            <td style="padding-top: 8%; width: 50%;">
                <table class="table-row">
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 40%;">CM-3572</td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 40%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 60%;"></td>
                        <td style="width: 40%;">003</td>
                    </tr>
                </table>

                <table class="table-row" style="padding-top: 15%;">
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">{{ $data['date'] }}</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">{{ $data['vessel']['bl_number'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">
                            @foreach($data['hbl']['packages'] as $package)
                                {{ $package['package_type'] }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">{{ count($data['hbl']['packages']) }}</td>
                    </tr>

                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">{{$data['hbl']['nic']}}</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">05/1321 -1325</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;">{{ $data['vessel']['vessel_name'] ?? ''}}</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"></td>
                        <td style="width: 70%;">{{ $data['by'] }}</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%;"></td>
                    </tr>
                </table>

                <table class="table-row" style="padding-top: 15%;">
                    <tr>
                        <td style="width: 30%;"></td>
                        <td style="width: 70%;">{{ $data['date'] }}</td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"></td>
                        <td style="width: 70%;">{{ $data['clearing_time'] }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
@endsection
