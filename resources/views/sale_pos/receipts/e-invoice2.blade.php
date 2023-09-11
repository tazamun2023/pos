<!-- business information here -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- <link rel="stylesheet" href="style.css"> -->
    <title>Receipt-{{$receipt_details->invoice_no}}</title>
</head>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300&display=swap');

    @page {
        margin: 10px !important;
    }

    body {

        justify-content: center;
        padding: 0 !important;
    }

    .container {
        padding: 0 !important
    }

    #company {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #invoice-num {
        margin-top: 4px;
    }

    #invoice-num>div {
        text-align: center
    }

    #datetime {
        margin-top: 0px;
        display: flex;
    }

    #datetime>div {
        /* text-align: center; */
        padding: 0 2px;
    }

    #datetime {
        margin: 6px 0;
    }

    .items-table {
        margin-top: 10px;
    }

    .items-table table th,
    .items-table table td {
        padding: 4px;
        text-align: center;
    }

    #totals {
        display: flex;
        margin-top: 10px;
        direction: rtl;
    }

    #totals>div:nth-of-type(2) {
        width: 100%;
        margin-inline-start: 4px;
        padding-inline-start: 4px;
        border-right: 1px solid rgba(146, 146, 146, 0.152);
    }

    #totals>div>div {
        padding: 4px;
    }

    #totals div {
        /* white-space: nowrap; */
    }

    #totals>div:nth-of-type(1) table {
        white-space: nowrap;
    }

    #totals>div:nth-of-type(2)>tr>td:nth-of-type(2) {
        width: 100%;
    }


    #totals>div:nth-of-type(2) table tr td:nth-of-type(2) {
        white-space: nowrap;
    }

    #total_row>td {
        padding-top: 5px;
    }

    #total_row>td>div {
        border-top: 1px dotted rgba(146, 146, 146, 0.152);
        padding-top: 5px !important;
    }

    #top-table {
        /* margin-bottom: 10px; */
    }

    table {
        width: 100%;
    }



    #logo-wrapper {
        width: 100%;
        text-align: center;
        margin: 10px auto;
    }

    #logo-wrapper img {
        max-height: 60px;
    }

    .text-center {
        text-align: center;
    }

    .ticket {
        width: 98%;
        max-width: 98%;
        margin-left: 6px;
        font-weight: 800;
        color: black;

    }


    #header-text {
        padding-top: 10px;
    }

    body {
        padding: 1 !important;
        margin: 0 !important;
        /* background-color: red !important; */
        width: 100% !important;
    }

    #installmentTable {
        font-size: 24px;
        margin: 15px 0 !important;
    }

    #installment_calculations tr>td:nth-of-type(1) {
        padding-inline-end: 10px;

    }

    #installmentTable td {
        font-size: 20px;
    }

    #gurantor-receipt {
        width: calc(100% - 100px);
        margin: auto;
    }

    #gurantor-receipt h1 {
        text-align: center;
    }

    #gurantor-receipt p {
        font-size: 20px;
    }

    #commodity-sale-statement p,
    #commodity-sale-statement div {
        font-size: 20px;
    }

</style>
<body>
@php
    $numbers = range(0, 9);
    function arToIn($num, $pattern)
    {
        if ($pattern == 'TYPE_DEFAULT') {
            $fmt = numfmt_create('ar', NumberFormatter::TYPE_DEFAULT);
        } elseif ($pattern == 'DECIMAL') {
            $fmt = numfmt_create('ar', NumberFormatter::DECIMAL);
        }
        return numfmt_format($fmt, $num);
    }

    function dateFormate($date)
    {
        $fmt = datefmt_create('ar_EG', IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Africa/Cairo', IntlDateFormatter::GREGORIAN, 'yyyy / M / d');
        return datefmt_format($fmt, $date);
    }
@endphp


<div class="ticket">


    @if(!empty($receipt_details->logo))
        <div class="text-box centered">
            <img style="max-height: 100px; width: auto;" src="{{$receipt_details->logo}}" alt="Logo">
        </div>
    @endif
    <div class="text-box">
        <!-- Logo -->
        <p class="centered">
            <!-- Header text -->
            @if(!empty($receipt_details->header_text))
                <span class="headings">{!! $receipt_details->header_text !!}</span>
                <br/>
            @endif

            <!-- business information here -->
            <!-- Title of receipt -->
            @if(!empty($receipt_details->invoice_heading))
                <br/><span class="headings">{!! $receipt_details->invoice_heading !!}</span>
            @endif
            @if(!empty($receipt_details->display_name))
                <span class="headings">
                        {{$receipt_details->display_name}}
                    </span>
                <br/>
            @endif

            @if(!empty($receipt_details->address))
                {!! $receipt_details->address !!}
                <br/>
            @endif

            @if(!empty($receipt_details->contact))
                {!! $receipt_details->contact !!}
            @endif
            {{--            @if(!empty($receipt_details->contact) && !empty($receipt_details->website))--}}
            {{--                ,--}}
            {{--            @endif--}}
            {{--            @if(!empty($receipt_details->website))--}}
            {{--                {{ $receipt_details->website }}--}}
            {{--            @endif--}}
            {{--            @if(!empty($receipt_details->location_custom_fields))--}}
            {{--                <br>{{ $receipt_details->location_custom_fields }}--}}
            {{--            @endif--}}

            @if(!empty($receipt_details->location_details['custom_field1']))
                <br/>{{ $receipt_details->location_details['custom_field1'] }}
                سجل تجاري
            @endif
            @if(!empty($receipt_details->location_details['custom_field2']))
                <br/>{{ $receipt_details->location_details['custom_field2'] }}
                الرقم الضريبي
            @endif


            @if(!empty($receipt_details->sub_heading_line1))
                {{ $receipt_details->sub_heading_line1 }}<br/>
            @endif
            @if(!empty($receipt_details->sub_heading_line2))
                {{ $receipt_details->sub_heading_line2 }}<br/>
            @endif
            @if(!empty($receipt_details->sub_heading_line3))
                {{ $receipt_details->sub_heading_line3 }}<br/>
            @endif
            @if(!empty($receipt_details->sub_heading_line4))
                {{ $receipt_details->sub_heading_line4 }}<br/>
            @endif
            @if(!empty($receipt_details->sub_heading_line5))
                {{ $receipt_details->sub_heading_line5 }}<br/>
            @endif

            {{--            @if(!empty($receipt_details->tax_info1))--}}
            @if(!empty($receipt_details->tax_label1)|| !empty($receipt_details->tax_info1))
                <br><b>{{ $receipt_details->tax_label1 }}</b> {{ $receipt_details->tax_info1 }}
            @endif

            @if(!empty($receipt_details->tax_label2) || !empty($receipt_details->tax_info2))
                <b>{{ $receipt_details->tax_label2 }}</b> {{ $receipt_details->tax_info2 }}
            @endif

            <!-- Title of receipt -->
            {{--            @if(!empty($receipt_details->invoice_heading))--}}
            {{--                <br/><span class="sub-headings">{!! $receipt_details->invoice_heading !!}</span>--}}
            {{--            @endif--}}
        </p>
    </div>
    @if(!empty($receipt_details->invoice_no_prefix) || !empty($receipt_details->invoice_no))
        <div class="border-top textbox-info">
            @if(!empty($receipt_details->invoice_no_prefix))
                <p class="f-left"><strong>{!! $receipt_details->invoice_no_prefix !!}</strong></p>
            @endif
            @if(!empty($receipt_details->invoice_no))
                <p class="f-right">
                    {{$receipt_details->invoice_no}}
                </p>
            @endif
        </div>
    @endif
    @if(!empty($receipt_details->date_label) || !empty($receipt_details->invoice_date))
        <div class="textbox-info">
            @if(!empty($receipt_details->date_label))
                <p class="f-left"><strong>{!! $receipt_details->date_label !!}</strong></p>
            @endif
            @if(!empty($receipt_details->invoice_date))
                <p class="f-right">
                    {{$receipt_details->invoice_date}}
                </p>
            @endif
        </div>
    @endif
    @if(!empty($receipt_details->due_date_label))
        <div class="textbox-info">
            @if(!empty($receipt_details->due_date_label))
                <p class="f-left"><strong>{{$receipt_details->due_date_label}}</strong></p>
            @endif
            @if(!empty($receipt_details->due_date))
                <p class="f-right">{{$receipt_details->due_date}}</p>
            @endif
        </div>
    @endif

    @if(!empty($receipt_details->sales_person_label) || !empty($receipt_details->sales_person))
        <div class="textbox-info">
            @if(!empty($receipt_details->sales_person_label))
                <p class="f-left"><strong>{{$receipt_details->sales_person_label}}</strong></p>
            @endif
            @if(!empty($receipt_details->sales_person))
                <p class="f-right">{{$receipt_details->sales_person}}</p>
            @endif
        </div>
    @endif
    @if(!empty($receipt_details->commission_agent_label) || !empty($receipt_details->commission_agent))
        <div class="textbox-info">
            @if(!empty($receipt_details->commission_agent_label))
                <p class="f-left"><strong>{{$receipt_details->commission_agent_label}}</strong></p>
            @endif
            @if(!empty($receipt_details->commission_agent))
                <p class="f-right">{{$receipt_details->commission_agent}}</p>
            @endif
        </div>
    @endif

    @if(!empty($receipt_details->brand_label) || !empty($receipt_details->repair_brand))
        <div class="textbox-info">
            @if(!empty($receipt_details->brand_label))
                <p class="f-left"><strong>{{$receipt_details->brand_label}}</strong></p>
            @endif
            @if(!empty($receipt_details->repair_brand))
                <p class="f-right">{{$receipt_details->repair_brand}}</p>
            @endif
        </div>
    @endif

    @if(!empty($receipt_details->device_label) || !empty($receipt_details->repair_device))
        <div class="textbox-info">
            @if(!empty($receipt_details->device_label))
                <p class="f-left"><strong>{{$receipt_details->device_label}}</strong></p>
            @endif
            @if(!empty($receipt_details->repair_device))
                <p class="f-right">{{$receipt_details->repair_device}}</p>
            @endif
        </div>
    @endif

    @if(!empty($receipt_details->model_no_label) || !empty($receipt_details->repair_model_no))
        <div class="textbox-info">
            @if(!empty($receipt_details->model_no_label))
                <p class="f-left"><strong>{{$receipt_details->model_no_label}}</strong></p>
            @endif
            @if(!empty($receipt_details->repair_model_no))
                <p class="f-right">{{$receipt_details->repair_model_no}}</p>
            @endif
        </div>
    @endif

    @if(!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no))
        <div class="textbox-info">
            @if(!empty($receipt_details->serial_no_label))
                <p class="f-left"><strong>{{$receipt_details->serial_no_label}}</strong></p>
            @endif
            @if(!empty($receipt_details->repair_serial_no))
                <p class="f-right">{{$receipt_details->repair_serial_no}}</p>
            @endif
        </div>
    @endif

    @if(!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status))
        <div class="textbox-info">
            @if(!empty($receipt_details->repair_status_label))
                <p class="f-left"><strong>
                        {!! $receipt_details->repair_status_label !!}
                    </strong></p>
            @endif
            @if(!empty($receipt_details->repair_status))
                <p class="f-right">
                    {{$receipt_details->repair_status}}
                </p>
            @endif
        </div>
    @endif

    @if(!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty))
        <div class="textbox-info">
            @if(!empty($receipt_details->repair_warranty_label))
                <p class="f-left"><strong>
                        {!! $receipt_details->repair_warranty_label !!}
                    </strong></p>
            @endif
            @if(!empty($receipt_details->repair_warranty))
                <p class="f-right">
                    {{$receipt_details->repair_warranty}}
                </p>
            @endif
        </div>
    @endif

    <!-- Waiter info -->
    @if(!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff))
        <div class="textbox-info">
            @if(!empty($receipt_details->service_staff_label))
                <p class="f-left"><strong>
                        {!! $receipt_details->service_staff_label !!}
                    </strong></p>
            @endif
            @if(!empty($receipt_details->service_staff))
                <p class="f-right">
                    {{$receipt_details->service_staff}}
                </p>
            @endif
        </div>
    @endif

    @if(!empty($receipt_details->table_label) || !empty($receipt_details->table))
        <div class="textbox-info">
            @if(!empty($receipt_details->table_label))
                <p class="f-left"><strong>
                        @if(!empty($receipt_details->table_label))
                            <b>{!! $receipt_details->table_label !!}</b>
                        @endif
                    </strong></p>
            @endif
            @if(!empty($receipt_details->table))
                <p class="f-right">
                    {{$receipt_details->table}}
                </p>
            @endif
        </div>
    @endif

    <!-- customer info -->
    @if(!empty($receipt_details->customer_label) || !empty($receipt_details->customer_info))
        <div class="textbox-info">
            @if(!empty($receipt_details->customer_label))
                <p style="vertical-align: top;"><strong>
                        {{$receipt_details->customer_label ?? ''}}
                    </strong></p>
            @endif
            @if(!empty($receipt_details->customer_info))
                <p><div class="bw">{!! $receipt_details->customer_info !!}</div></p>
            @endif
                @if(!empty($receipt_details->customer_tax_label) || !empty($receipt_details->customer_tax_number))
                    <p><div class="bw">
                        <span> {{ $receipt_details->customer_tax_label }}</span>
                        <span> {{ $receipt_details->customer_tax_number }}</span>
                    </div></p>
                @endif

        </div>
    @endif
{{--        @if(!empty($receipt_details->customer_tax_label) || !empty($receipt_details->customer_tax_number))--}}
{{--            <div class="textbox-info">--}}
{{--                @if(!empty($receipt_details->customer_tax_label))--}}
{{--                    <p class="f-left"><strong>--}}
{{--                            {{ $receipt_details->customer_tax_label }}--}}
{{--                        </strong></p>--}}
{{--                @endif--}}
{{--                @if(!empty($receipt_details->customer_tax_number))--}}
{{--                    <p class="f-right">--}}
{{--                        {{ $receipt_details->customer_tax_number }}--}}
{{--                    </p>--}}
{{--                @endif--}}
{{--            </div>--}}
{{--        @endif--}}

    @if(!empty($receipt_details->client_id_label))
        <div class="textbox-info">
            @if(!empty($receipt_details->client_id_label))
                <p class="f-left"><strong>
                        {{ $receipt_details->client_id_label }}
                    </strong></p>
            @endif
            @if(!empty($receipt_details->client_id))
                <p class="f-right">
                    {{ $receipt_details->client_id }}
                </p>
            @endif
        </div>
    @endif


    @if(!empty($receipt_details->customer_custom_fields))
        <div class="textbox-info">
            <p class="centered">
                {!! $receipt_details->customer_custom_fields !!}
            </p>
        </div>
    @endif

    @if(!empty($receipt_details->customer_rp_label))
        <div class="textbox-info">
            @if(!empty($receipt_details->customer_rp_label))
                <p class="f-left"><strong>
                        {{ $receipt_details->customer_rp_label }}
                    </strong></p>
            @endif
            @if(!empty($receipt_details->customer_total_rp))
                <p class="f-right">
                    {{ $receipt_details->customer_total_rp }}
                </p>
            @endif
        </div>
    @endif
    @if(!empty($receipt_details->shipping_custom_field_1_label))
        <div class="textbox-info">
            @if(!empty($receipt_details->shipping_custom_field_1_label))
                <p class="f-left"><strong>
                        {!!$receipt_details->shipping_custom_field_1_label!!}
                    </strong></p>
            @endif
            @if(!empty($receipt_details->shipping_custom_field_1_value))
                <p class="f-right">
                    {!!$receipt_details->shipping_custom_field_1_value !!}
                </p>
            @endif
        </div>
    @endif
    @if(!empty($receipt_details->shipping_custom_field_2_label))
        <div class="textbox-info">
            @if(!empty($receipt_details->shipping_custom_field_2_label))
                <p class="f-left"><strong>
                        {!!$receipt_details->shipping_custom_field_2_label!!}
                    </strong></p>
            @endif
            @if(!empty($receipt_details->shipping_custom_field_2_value))
                <p class="f-right">
                    {!!$receipt_details->shipping_custom_field_2_value !!}
                </p>
            @endif
        </div>
    @endif
    @if(!empty($receipt_details->shipping_custom_field_3_label))
        <div class="textbox-info">
            @if(!empty($receipt_details->shipping_custom_field_3_label))
                <p class="f-left"><strong>
                        {!! $receipt_details->shipping_custom_field_3_label !!}
                    </strong></p>
            @endif
            @if(!empty($receipt_details->shipping_custom_field_3_value))
                <p class="f-right">
                    {!!$receipt_details->shipping_custom_field_3_value !!}
                </p>
            @endif
        </div>
    @endif
    @if(!empty($receipt_details->shipping_custom_field_4_label))
        <div class="textbox-info">
            @if(!empty($receipt_details->shipping_custom_field_4_label))
                <p class="f-left"><strong>
                        {!!$receipt_details->shipping_custom_field_4_label!!}
                    </strong></p>
            @endif
            @if(!empty($receipt_details->shipping_custom_field_4_value))
                <p class="f-right">
                    {!!$receipt_details->shipping_custom_field_4_value !!}
                </p>
            @endif
        </div>
    @endif
    @if(!empty($receipt_details->shipping_custom_field_5_label))
        <div class="textbox-info">
            @if(!empty($receipt_details->shipping_custom_field_5_label))
                <p class="f-left"><strong>
                        {!!$receipt_details->shipping_custom_field_5_label!!}
                    </strong></p>
            @endif
            @if(!empty($receipt_details->shipping_custom_field_5_value))
                <p class="f-right">
                    {!! $receipt_details->shipping_custom_field_5_value !!}
                </p>
            @endif
        </div>
    @endif
    @if(!empty($receipt_details->sale_orders_invoice_no))
        <div class="textbox-info">
            <p class="f-left"><strong>
                    @lang('restaurant.order_no')
                </strong></p>
            @if(!empty($receipt_details->sale_orders_invoice_no))
                <p class="f-right">
                    {!!$receipt_details->sale_orders_invoice_no !!}
                </p>
            @endif
        </div>
    @endif

    @if(!empty($receipt_details->sale_orders_invoice_date))
        <div class="textbox-info">
            <p class="f-left"><strong>
                    @lang('lang_v1.order_dates')
                </strong></p>
            @if(!empty($receipt_details->sale_orders_invoice_date))
                <p class="f-right">
                    {!!$receipt_details->sale_orders_invoice_date !!}
                </p>
            @endif
        </div>
    @endif
    <table style="margin-top: 25px !important" class="border-bottom width-100 table-f-12 mb-10">
        <thead class="border-bottom-dotted">
        <tr>
            <th class="serial_number">#</th>
            <th class="description" width="30%">
                {{$receipt_details->table_product_label}}
            </th>
            <th class="quantity text-right">
                {{$receipt_details->table_qty_label}}
            </th>
            @if(empty($receipt_details->hide_price))
                <th class="unit_price text-right">
                    {{$receipt_details->table_unit_price_label}}
                </th>
                @if(!empty($receipt_details->item_discount_label))
                    <th class="text-right" width="15%">{{$receipt_details->item_discount_label}}</th>
                @endif
                <th class="price text-right">{{$receipt_details->table_subtotal_label}}</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @forelse($receipt_details->lines as $line)
            <tr>
                <td class="serial_number" style="vertical-align: top;">
                    {{$loop->iteration}}
                </td>
                <td class="description">
                    {{$line['name']}} {{$line['product_variation']}} {{$line['variation']}}
                    @if(!empty($line['sub_sku'])), {{$line['sub_sku']}} @endif @if(!empty($line['brand'])), {{$line['brand']}} @endif @if(!empty($line['cat_code'])), {{$line['cat_code']}}@endif
                    @if(!empty($line['product_custom_fields'])), {{$line['product_custom_fields']}} @endif
                    @if(!empty($line['sell_line_note']))
                        <br>
                        <span class="f-8">
                                {{$line['sell_line_note']}}
                                </span>
                    @endif
                    @if(!empty($line['lot_number']))<br> {{$line['lot_number_label']}}:  {{$line['lot_number']}} @endif
                    @if(!empty($line['product_expiry'])), {{$line['product_expiry_label']}}:  {{$line['product_expiry']}} @endif
                    @if(!empty($line['warranty_name']))
                        <br>
                        <small>
                            {{$line['warranty_name']}}
                        </small>
                    @endif
                    @if(!empty($line['warranty_exp_date']))
                        <small>
                            - {{@format_date($line['warranty_exp_date'])}}
                        </small>
                    @endif
                    @if(!empty($line['warranty_description']))
                        <small> {{$line['warranty_description'] ?? ''}}</small>
                    @endif
                </td>
                <td class="quantity text-right">{{$line['quantity']}} {{$line['units']}}</td>
                @if(empty($receipt_details->hide_price))
                    <td class="unit_price text-right">{{$line['unit_price_before_discount']}}</td>
                    @if(!empty($receipt_details->item_discount_label))
                        <td class="text-right">
                            {{$line['line_discount'] ?? '0.00'}}
                        </td>
                    @endif
                    <td class="price text-right">{{$line['line_total']}}</td>
                @endif
            </tr>
            @if(!empty($line['modifiers']))
                @foreach($line['modifiers'] as $modifier)
                    <tr>
                        <td>
                            &nbsp;
                        </td>
                        <td>
                            {{$modifier['name']}} {{$modifier['variation']}}
                            @if(!empty($modifier['sub_sku'])), {{$modifier['sub_sku']}} @endif @if(!empty($modifier['cat_code'])), {{$modifier['cat_code']}}@endif
                            @if(!empty($modifier['sell_line_note']))({{$modifier['sell_line_note']}}) @endif
                        </td>
                        <td class="text-right">{{$modifier['quantity']}} {{$modifier['units']}} </td>
                        @if(empty($receipt_details->hide_price))
                            <td class="text-right">{{$modifier['unit_price_inc_tax']}}</td>
                            @if(!empty($receipt_details->item_discount_label))
                                <td class="text-right">0.00</td>
                            @endif
                            <td class="text-right">{{$modifier['line_total']}}</td>
                        @endif
                    </tr>
                @endforeach
            @endif
            @endforeach
            <tr>
                <td @if(!empty($receipt_details->item_discount_label)) colspan="6" @else colspan="5" @endif>&nbsp;</td>
            </tr>
        </tbody>
    </table>
    @if(!empty($receipt_details->total_quantity_label))
        <div class="flex-box">
            <p class="left text-right">
                {!! $receipt_details->total_quantity_label !!}
            </p>
            <p class="width-50 text-right">
                {{$receipt_details->total_quantity}}
            </p>
        </div>
    @endif
    @if(empty($receipt_details->hide_price))
        <div class="flex-box">
            <p class="left text-right sub-headings">
                {!! $receipt_details->subtotal_label !!}
            </p>
            <p class="width-50 text-right sub-headings">
                {{$receipt_details->subtotal}}
            </p>
        </div>

        <!-- Shipping Charges -->
        @if(!empty($receipt_details->shipping_charges))
            <div class="flex-box">
                <p class="left text-right">
                    {!! $receipt_details->shipping_charges_label !!}
                </p>
                <p class="width-50 text-right">
                    {{$receipt_details->shipping_charges}}
                </p>
            </div>
        @endif

        @if(!empty($receipt_details->packing_charge))
            <div class="flex-box">
                <p class="left text-right">
                    {!! $receipt_details->packing_charge_label !!}
                </p>
                <p class="width-50 text-right">
                    {{$receipt_details->packing_charge}}
                </p>
            </div>
        @endif

        <!-- Discount -->
        @if( !empty($receipt_details->discount) )
            <div class="flex-box">
                <p class="width-50 text-right">
                    {!! $receipt_details->discount_label !!}
                </p>

                <p class="width-50 text-right">
                    (-) {{$receipt_details->discount}}
                </p>
            </div>
        @endif

        @if( !empty($receipt_details->total_line_discount) )
            <div class="flex-box">
                <p class="width-50 text-right">
                    {!! $receipt_details->line_discount_label !!}
                </p>

                <p class="width-50 text-right">
                    (-) {{$receipt_details->total_line_discount}}
                </p>
            </div>
        @endif

        @if(!empty($receipt_details->reward_point_label) )
            <div class="flex-box">
                <p class="width-50 text-right">
                    {!! $receipt_details->reward_point_label !!}
                </p>

                <p class="width-50 text-right">
                    (-) {{$receipt_details->reward_point_amount}}
                </p>
            </div>
        @endif

        @if( !empty($receipt_details->tax) )
            <div class="flex-box">
                <p class="width-50 text-right">
                    {!! $receipt_details->tax_label !!}
                </p>
                <p class="width-50 text-right">
                    (+) {{$receipt_details->tax}}
                </p>
            </div>
        @endif

        @if( $receipt_details->round_off_amount > 0)
            <div class="flex-box">
                <p class="width-50 text-right">
                    {!! $receipt_details->round_off_label !!}
                </p>
                <p class="width-50 text-right">
                    {{$receipt_details->round_off}}
                </p>
            </div>
        @endif

        <div class="flex-box">
            <p class="width-50 text-right sub-headings">
                {!! $receipt_details->total_label !!}
            </p>
            <p class="width-50 text-right sub-headings">
                {{$receipt_details->total}}
            </p>
        </div>
        @if(!empty($receipt_details->total_in_words))
            <p colspan="2" class="text-right mb-0">
                <small>
                    ({{$receipt_details->total_in_words}})
                </small>
            </p>
        @endif
        @if(!empty($receipt_details->payments))
            @foreach($receipt_details->payments as $payment)
                <div class="flex-box">
                    <p class="width-50 text-right">{{$payment['method']}} ({{$payment['date']}}) </p>
                    <p class="width-50 text-right">{{$payment['amount']}}</p>
                </div>
            @endforeach
        @endif

        <!-- Total Paid-->
        @if(!empty($receipt_details->total_paid))
            <div class="flex-box" style="font-size: 15px !important;">
                <p class="width-50 text-right">
                    {!! $receipt_details->total_paid_label !!} {{$receipt_details->total_paid}}
                </p>
                <p class="width-50 text-right">
                    {{$receipt_details->total_paid}}
                </p>
            </div>
        @endif

        <!-- Total Due-->
        @if(!empty($receipt_details->total_due))
            <div class="flex-box">
                <p class="width-50 text-right">
                    {!! $receipt_details->total_due_label !!}
                </p>
                <p class="width-50 text-right">
                    {{$receipt_details->total_due}}
                </p>
            </div>
        @endif

        @if(!empty($receipt_details->all_due))
            <div class="flex-box">
                <p class="width-50 text-right">
                    {!! $receipt_details->all_bal_label !!}
                </p>
                <p class="width-50 text-right">
                    {{$receipt_details->all_due}}
                </p>
            </div>
        @endif
    @endif
    <div class="border-bottom width-100">&nbsp;</div>
    @if(empty($receipt_details->hide_price))
        <!-- tax -->
        @if(!empty($receipt_details->taxes))
            <table class="border-bottom width-100 table-f-12">
                @foreach($receipt_details->taxes as $key => $val)
                    <tr>
                        <td class="left">{{$key}}</td>
                        <td class="right">{{$val}}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    @endif


    @if(!empty($receipt_details->additional_notes))
        <p class="centered" >
            {!! nl2br($receipt_details->additional_notes) !!}
        </p>
    @endif

        {{-- Barcode --}}
        @if($receipt_details->show_barcode)
            <br>
            <div class="row">
                <div class="col-xs-12">
                    <img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2,30,array(39, 48, 54), true)}}">
                </div>
            </div>
        @endif

        @if($receipt_details->show_qr_code && !empty($receipt_details->qr_code_details))
            @php
                $qr_code_text = implode(', ', $receipt_details->qr_code_details);
            @endphp
            {{--				<img class="center-block mt-5" src="data:image/png;base64,{{DNS2D::getBarcodePNG($qr_code_text, 'QRCODE', 4, 4, [39, 48, 54])}}">--}}
            @include('sale_pos.partials.qr_code')
        @endif

    @if(!empty($receipt_details->footer_text))
        <p class="centered">
            {!! $receipt_details->footer_text !!}
        </p>
    @endif

</div>
<!-- <button id="btnPrint" class="hidden-print">Print</button>
<script src="script.js"></script> -->
</body>
</html>

<style type="text/css">
    .f-8 {
        font-size: 8px !important;
    }
    @media print {
        * {
            font-size: 12px;
            font-family: 'Times New Roman';
            word-break: break-all;
        }
        .f-8 {
            font-size: 8px !important;
        }

        .headings{
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .sub-headings{
            font-size: 15px !important;
            font-weight: 700 !important;
        }

        .border-top{
            border-top: 1px solid #242424;
        }
        .border-bottom{
            border-bottom: 1px solid #242424;
        }

        .border-bottom-dotted{
            border-bottom: 1px dotted darkgray;
        }

        td.serial_number, th.serial_number{
            width: 5%;
            max-width: 5%;
        }

        td.description,
        th.description {
            width: 35%;
            max-width: 35%;
        }

        td.quantity,
        th.quantity {
            width: 15%;
            max-width: 15%;
            word-break: break-all;
        }
        td.unit_price, th.unit_price{
            width: 25%;
            max-width: 25%;
            word-break: break-all;
        }

        td.price,
        th.price {
            width: 20%;
            max-width: 20%;
            word-break: break-all;
        }

        .centered {
            text-align: center;
            align-content: center;
        }

        .ticket {
            width: 100%;
            max-width: 100%;
        }

        img {
            max-width: inherit;
            width: auto;
        }

        .hidden-print,
        .hidden-print * {
            display: none !important;
        }
    }
    .table-info {
        width: 100%;
    }
    .table-info tr:first-child td, .table-info tr:first-child th {
        padding-top: 8px;
    }
    .table-info th {
        text-align: left;
    }
    .table-info td {
        text-align: right;
    }
    .logo {
        float: left;
        width:35%;
        padding: 10px;
    }

    .text-with-image {
        float: left;
        width:65%;
    }
    .text-box {
        width: 100%;
        height: auto;
    }

    .textbox-info {
        clear: both;
    }
    .textbox-info p {
        margin-bottom: 0px
    }
    .flex-box {
        display: flex;
        width: 100%;
    }
    .flex-box p {
        width: 50%;
        margin-bottom: 0px;
        white-space: nowrap;
    }

    .table-f-12 th, .table-f-12 td {
        font-size: 12px;
        word-break: break-word;
    }

    .bw {
        word-break: break-word;
    }
</style>