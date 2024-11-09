@if($settings)
    @if($logoPath)
        <div>
            <img style="max-width: 100px; max-height: 50px; margin-right: -15px; float: left; margin-top: 10px"
                 src="{{ $logoPath }}"
                 alt="app_logo">
        </div>
    @endif
    <div class="header">
        <h3>{{$settings->invoice_header_title ?: 'LAKSIRI CARGO SERVICE'}}</h3>
        <h4>{{$settings->invoice_header_subtitle ?: 'BAB AL TAWASUL TRADING EST.'}}</h4>
        <h6>{{$settings->invoice_header_address ?: 'P.O.Box: 245452 - Riyadh: 11312 - K.S.A.'}}</h6>
        <h5>{{$settings->invoice_header_telephone ?: 'Tel. Office: +966 55 304 4684 / 50 926 5586 / 55 611 2199 / 54 076 9814'}}</h5>
    </div>
@endif

<style>
    .header {
        color: #2b5084;
        display: flex;
        justify-content: center;
        text-align: center;
    }

    .header > h2,
    h3,
    h6,
    h5 {
        margin-top: 0;
        margin-bottom: 0;
        padding-top: 0;
        padding-bottom: 0;
        font-family: "Roboto", sans-serif;
    }

    .header > h3 {
        margin-bottom: -2px;
    }

    .header > h4 {
        margin-top: -4px;
        margin-bottom: -5px;
    }
</style>
