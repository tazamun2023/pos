@php
    $totals = ['taxable_value' => 0];
@endphp


<table style="width:100%;"> 
   <thead>
    <tr>
        <th width="50%" style="text-align: left !importent" rowspan="2">@if($receipt_details->show_barcode)
            <br/>
            <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2,30,array(39, 48, 54), true)}}">
        @endif

        @if($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
            <img class="mt-5" src="data:image/png;base64,{{DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE')}}">
        @endif</th>
        <th style="text-align: right" colspan="2">
            <h3>@if(!empty($receipt_details->invoice_heading))
                <p class="" style="font-weight: bold; font-size: 20px !important;">{!! $receipt_details->invoice_heading !!}</p>
            @endif</h3>
        </th>

    </tr>
    <tr>
        <th style="text-align: right"> @if(!empty($receipt_details->invoice_no_prefix))
            {!! $receipt_details->invoice_no_prefix !!}
        @endif : {{$receipt_details->invoice_no}} </th>
        <th style="text-align: right">
        @if(!empty($receipt_details->date_label))
                {{$receipt_details->invoice_date}}
                {!! $receipt_details->date_label !!}
        @endif
        <br>
        @if(!empty($receipt_details->due_date_label))
                <span>{{$receipt_details->due_date_label}}</span>
                {{$receipt_details->due_date ?? ''}}
        @endif
    </th>
    </tr>
    </thead>
   </table>
   <br>
   <br>
   <table width="100%" style="padding-top:20px;">
    <tbody style="margin-top:100px;">
        @if(!empty($receipt_details->display_name))
    <tr>
        <td colspan="4" style="text-align: right"><strong>  {{$receipt_details->display_name}}</strong></td>
    </tr>
    <tr>
        <td>{{ $receipt_details->saller_id_label }}: {{ $receipt_details->saller_id }}<br>

            
        <td>Tax No {{ $receipt_details->custom_field1, $receipt_details->custom_field2 }}<br>
{{--            @if(!empty($receipt_details->custom_field1)){{$receipt_details->tax_info1}}@else -- @endif <br></td>--}}
        <td>
            Contact <br>
            @if(!empty($receipt_details->contact))
                {!! $receipt_details->contact !!}
            @endif</td>
        <td style="text-align: right">Address <br> @if(!empty($receipt_details->address))
            {!! $receipt_details->address !!}
        @endif</td>
    </tr>
    @endif
    <br>
    <br>
    <tr style="margin-top: 30px;">
        <td colspan="5">&nbsp; &nbsp; &nbsp; <br></td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: right;"> 
           <strong style="padding-top:30px;">
            @if(!empty($receipt_details->customer_name))
            {{$receipt_details->customer_name}}
            @endif
           </strong>
        </td>
    </tr>
    <tr>
        <td> 
            @if(!empty($receipt_details->client_id_label))
            {{ $receipt_details->client_id_label }}
            @else
{{--            Client id: --}}
            @endif
            <br>
            @if(!empty($receipt_details->client_id_label))
            
            {{ $receipt_details->client_id }}
            @else
{{--            ----}}
           @endif</td>
        <td> @if(!empty($receipt_details->customer_tax_label))
           {{ $receipt_details->customer_tax_label }}
            @else
            Tax No: {{ $receipt_details->location_custom_fields }}
           @endif
           <br>
           @if(!empty($receipt_details->customer_tax_label))
             {{ $receipt_details->customer_tax_number }}
             @else
{{--             ----}}
        @endif</td>
        <td> 
            Contact: <br>
            @if(!empty($receipt_details->contact))
                {!! $receipt_details->contact !!}
            @endif
            
        </td>
        <td  style="text-align: right"> @if(!empty($receipt_details->customer_custom_fields))
            <br/>{!! $receipt_details->customer_custom_fields !!}
            @else
{{--            Address <br>--}}
            {!! $receipt_details->customer_info_address !!}
        @endif</td>
    </tr>
   </tbody>
</table>



<table style="width:100%;">
    <tbody>
    <tr>
        <td>

            <!-- business information here -->
            <div class="row invoice-info">


                <!-- Table information-->
                    @if(!empty($receipt_details->table_label) || !empty($receipt_details->table))
                        <p>
                            @if(!empty($receipt_details->table_label))
                                {!! $receipt_details->table_label !!}
                            @endif
                            {{$receipt_details->table}}
                        </p>
                    @endif

                <!-- Waiter info -->
                    @if(!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff))
                        <p>
                            @if(!empty($receipt_details->service_staff_label))
                                {!! $receipt_details->service_staff_label !!}
                            @endif
                            {{$receipt_details->service_staff}}
                        </p>
                    @endif



                    <div class="word-wrap">

                        <p class="text-right color-555">

                            @if(!empty($receipt_details->brand_label) || !empty($receipt_details->repair_brand))
                                @if(!empty($receipt_details->brand_label))
                                    <span class="pull-left">
						<strong>{!! $receipt_details->brand_label !!}</strong>
					</span>
                                @endif
                                {{$receipt_details->repair_brand}}<br>
                            @endif


                            @if(!empty($receipt_details->device_label) || !empty($receipt_details->repair_device))
                                @if(!empty($receipt_details->device_label))
                                    <span class="pull-left">
						<strong>{!! $receipt_details->device_label !!}</strong>
					</span>
                                @endif
                                {{$receipt_details->repair_device}}<br>
                            @endif

                            @if(!empty($receipt_details->model_no_label) || !empty($receipt_details->repair_model_no))
                                @if(!empty($receipt_details->model_no_label))
                                    <span class="pull-left">
						<strong>{!! $receipt_details->model_no_label !!}</strong>
					</span>
                                @endif
                                {{$receipt_details->repair_model_no}} <br>
                            @endif

                            @if(!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no))
                                @if(!empty($receipt_details->serial_no_label))
                                    <span class="pull-left">
						<strong>{!! $receipt_details->serial_no_label !!}</strong>
					</span>
                                @endif
                                {{$receipt_details->repair_serial_no}}<br>
                            @endif
                            @if(!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status))
                                @if(!empty($receipt_details->repair_status_label))
                                    <span class="pull-left">
						<strong>{!! $receipt_details->repair_status_label !!}</strong>
					</span>
                                @endif
                                {{$receipt_details->repair_status}}<br>
                            @endif

                            @if(!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty))
                                @if(!empty($receipt_details->repair_warranty_label))
                                    <span class="pull-left">
						<strong>{!! $receipt_details->repair_warranty_label !!}</strong>
					</span>
                                @endif
                                {{$receipt_details->repair_warranty}}
                                <br>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="row">
                @includeIf('sale_pos.receipts.partial.common_repair_invoice')
            </div>
            <div class="row color-555">
                <div class="col-xs-12">
                    <br/>
                    <table class="table table-bordered table-no-top-cell-border table-slim">
                        <thead>
                        <tr style="background-color: #aaaaaa !important; color: #000 !important; border: 1px solid grey; font-size: 15px !important font-weight: bold;" class="table-no-side-cell-border table-no-top-cell-border text-center">
                            <td style="background-color: #aaaaaa !important; color: #000 !important;" width="5%">#</td>

                            <td style="background-color: #aaaaaa !important; color: #000 !important;" class="text-left" width="55%">
                                {!! $receipt_details->table_product_label !!}
                            </td>

                            @if($receipt_details->show_cat_code == 1)
                                <td style="background-color: #aaaaaa !important; color: #000 !important;" class="text-right">{!! $receipt_details->cat_code_label !!}</td>
                            @endif

                            <td style="background-color: #aaaaaa !important; color: #000 !important;" class="text-right" width="10%">
                                {!! $receipt_details->table_qty_label !!}
                            </td>
                            <td style="background-color: #aaaaaa !important; color: #000 !important;" class="text-right" width="17%">
                                {!! $receipt_details->table_unit_price_label !!} <span class="small color-black"> ({{$receipt_details->currency['symbol']}})</span>
                            </td>
                        <!-- <td style="background-color: #d2d6de !important; color: #000 !important;">
						{!! $receipt_details->line_discount_label !!}
                                </td> -->


{{--                            @if(!empty($receipt_details->table_tax_headings))--}}

{{--                                @foreach($receipt_details->table_tax_headings as $tax_heading)--}}
{{--                                    <td style="background-color: #d2d6de !important; color: #000 !important;" class="word-wrap text-right">--}}
{{--                                        {{$tax_heading}} <span class="small color-black"> ({{$receipt_details->currency['symbol']}})</span>--}}
{{--                                    </td>--}}

{{--                                    @php--}}
{{--                                        $totals[$tax_heading] = 0;--}}
{{--                                    @endphp--}}
{{--                                @endforeach--}}

{{--                            @endif--}}

                            <td style="background-color: #aaaaaa !important; color: #000 !important;" width="13%" class="text-right">
                                {!! $receipt_details->table_subtotal_label !!}  <span class="small color-black"> ({{$receipt_details->currency['symbol']}})</span>
                            </td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($receipt_details->lines as $line)
                            <tr>
                                <td class="text-center">
                                    {{$loop->iteration}}
                                </td>
                                <td class="text-left" style="word-break: break-all;">
                                    @if(!empty($line['image']))
                                        <img src="{{$line['image']}}" alt="Image" width="50" style="float: left; margin-right: 8px;">
                                    @endif
                                    {{$line['name']}} {{$line['product_variation']}} {{$line['variation']}}
                                    @if(!empty($line['sub_sku'])), {{$line['sub_sku']}} @endif @if(!empty($line['brand'])), {{$line['brand']}} @endif
                                    @if(!empty($line['sell_line_note']))
                                        <br>
                                        <small class="text-muted">
                                            {{$line['sell_line_note']}}
                                        </small>
                                    @endif
                                    @if(!empty($line['lot_number']))<br> {{$line['lot_number_label']}}:  {{$line['lot_number']}} @endif
                                    @if(!empty($line['product_expiry'])), {{$line['product_expiry_label']}}:  {{$line['product_expiry']}} @endif

                                    @if(!empty($line['warranty_name'])) <br><small>{{$line['warranty_name']}} </small>@endif @if(!empty($line['warranty_exp_date'])) <small>- {{@format_date($line['warranty_exp_date'])}} </small>@endif
                                    @if(!empty($line['warranty_description'])) <small> {{$line['warranty_description'] ?? ''}}</small>@endif
                                </td>

                                @if($receipt_details->show_cat_code == 1)
                                    <td class="text-right">
                                        @if(!empty($line['cat_code']))
                                            {{$line['cat_code']}}
                                        @endif
                                    </td>
                                @endif

                                <td class="text-right">
                                    {{$line['quantity']}} {{$line['units']}}
                                </td>
                                <td class="text-right">
                                    {{$line['unit_price_before_discount']}}
                                </td>

{{--                                <td class="text-right">--}}
{{--                                    <span class="display_currency" data-currency_symbol="false">--}}
{{--                                        {{$line['price_exc_tax']}}--}}
{{--                                    </span>--}}

{{--                                    @php--}}
{{--                                        $totals['taxable_value'] += $line['price_exc_tax'];--}}
{{--                                    @endphp--}}
{{--                                </td>--}}



{{--                                @if(!empty($receipt_details->table_tax_headings))--}}

{{--                                    @foreach($receipt_details->table_tax_headings as $tax_heading)--}}
{{--                                    @endforeach--}}

{{--                                @endif--}}



                                <td class="text-right">
                                    {{$line['line_total']}}
                                </td>
                            </tr>
                            {{-- @if(!empty($line['modifiers']))
                                @foreach($line['modifiers'] as $modifier)
                                    <tr>
                                        <td class="text-center">
                                            &nbsp;
                                        </td>
                                        <td>
                                            {{$modifier['name']}} {{$modifier['variation']}}
                                            @if(!empty($modifier['sub_sku'])), {{$modifier['sub_sku']}} @endif
                                            @if(!empty($modifier['sell_line_note']))({{$modifier['sell_line_note']}}) @endif
                                        </td>

                                        @if($receipt_details->show_cat_code == 1)
                                            <td>
                                                @if(!empty($modifier['cat_code']))
                                                    {{$modifier['cat_code']}}
                                                @endif
                                            </td>
                                        @endif

                                        <td class="text-right">
                                            {{$modifier['quantity']}} {{$modifier['units']}}
                                        </td>
                                        <td class="text-right">
                                            &nbsp;
                                        </td>
                                        <td class="text-center">
                                            &nbsp;
                                        </td>
                                        <td class="text-center">
                                            &nbsp;
                                        </td>
                                        <td class="text-center">
                                            {{$modifier['unit_price_exc_tax']}}
                                        </td>
                                        <td class="text-right">
                                            {{$modifier['line_total']}}
                                        </td>
                                    </tr>
                                @endforeach
                            @endif --}}
                        @endforeach

                        @php
                            $lines = count($receipt_details->lines);
                        @endphp

                        @for ($i = $lines; $i < 5; $i++)
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
{{--                                <td>&nbsp;</td>--}}
                                <!-- <td>&nbsp;</td> -->

                                @if(!empty($receipt_details->table_tax_headings))
                                    @foreach($receipt_details->table_tax_headings as $tax_heading)
                                        <td>&nbsp;</td>
                                    @endforeach
                                @endif

                                @if($receipt_details->show_cat_code == 1)
                                    <td>&nbsp;</td>
                                @endif
                            </tr>
                        @endfor
                        <tr style="border: 1px solid grey">

                            @php
                                $colspan = 1;
                            @endphp
                            @if($receipt_details->show_cat_code == 1)
                                @php
                                    $colspan =2;
                                @endphp
                            @endif
                            <th class="text-right" colspan="3" style="background-color: #d2d6de !important;">
                            @if(!empty($receipt_details->total_quantity_label))
                            {!! $receipt_details->total_quantity_label !!}
                                {{$receipt_details->total_quantity}}

                                @endif
                            </th>
                            <th colspan="{{$colspan}}" class="text-right"
                                style="background-color: #d2d6de !important;">
                                Total
                            </th>

                            <th class="text-right" style="background-color: #d2d6de !important;">
						<span class="display_currency" data-currency_symbol="false">
							{{$receipt_details->subtotal_unformatted}}
						</span>
                            </th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row invoice-info color-555" style="page-break-inside: avoid !important;">
                <div class="col-md-6 invoice-col width-60">
                    <table class="table table-bordered table-no-top-cell-border table-slim" style="width: 100%;">
                        @if(!empty($receipt_details->payments))
                            <thead>
{{--                            @foreach($receipt_details->payments as $payment)--}}
                                <tr style="background-color: #d2d6de !important; color: #000 !important; font-size: 15px !important font-weight: bold; border: 1px solid grey" class="table-no-side-cell-border table-no-top-cell-border text-center">
                                    <td style="background-color: #d2d6de !important; color: #000 !important;" width="5%">#</td>
                                    <td style="background-color: #d2d6de !important; color: #000 !important;" class="text-left" width="30%">@lang('lang_v1.payment_method')</td>
                                    <td style="background-color: #d2d6de !important; color: #000 !important;" class="text-left" width="30%">@lang('lang_v1.payment_amount')</td>
                                    <td style="background-color: #d2d6de !important; color: #000 !important;" class="text-left" width="30%">@lang('lang_v1.payment_date')</td>
{{--                                    <td>{{$payment['method']}}</td>--}}
{{--                                    <td>{{$payment['amount']}}</td>--}}
{{--                                    <td>{{$payment['date']}}</td>--}}
                                </tr>
{{--                            @endforeach--}}
                            </thead>
                            <tbody>
                                @foreach($receipt_details->payments as $payment)
                                    <tr class="table-no-side-cell-border table-no-top-cell-border text-center" style="border-bottom: 1px dashed gray;">
                                        <td class="mb-2">{{$loop->iteration}}</td>
                                        <td class="text-center mb-5" style="padding: 5px;">{{$payment['method']}}</td>
                                        <td class="text-center mb-5" style="padding: 5px;">{{$payment['amount']}}</td>
                                        <td class="text-center mb-5" style="padding: 5px;">{{$payment['date']}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        @endif
                    </table>
{{--                    @if(!empty($receipt_details->total_paid))--}}
{{--                        <div class="text-right font-23 color-555" style="border: 2px solid !important; padding: 8px; margin-top: 15px">--}}
{{--                            <span class="pull-left">{!! $receipt_details->total_paid_label !!}</span>--}}
{{--                            {{$receipt_details->total_paid}}--}}
{{--                        </div>--}}
{{--                    @endif--}}
                    {{--		<b class="pull-left">@lang('lang_v1.authorized_signatory')</b>--}}
                </div>

                <div class="col-md-6 invoice-col width-40">
                    <table class="table-no-side-cell-border table-no-top-cell-border width-100 table-slim">
                        <tbody>
{{--                        @if(!empty($receipt_details->total_quantity_label))--}}
{{--                            <tr class="color-555">--}}
{{--                                <td style="width:50%">--}}
{{--                                    {!! $receipt_details->total_quantity_label !!}--}}
{{--                                </td>--}}
{{--                                <td class="text-right">--}}
{{--                                    {{$receipt_details->total_quantity}}--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                        @endif--}}
                        <tr class="color-555">
                            <td style="width:50%">
                                {!! $receipt_details->subtotal_label !!}
                            </td>
                            <td class="text-right">
                                {{$receipt_details->subtotal}}
                            </td>
                        </tr>

                        <!-- Shipping Charges -->
                        @if(!empty($receipt_details->shipping_charges))
                            <tr class="color-555">
                                <td style="width:50%">
                                    {!! $receipt_details->shipping_charges_label !!}
                                </td>
                                <td class="text-right">
                                    {{$receipt_details->shipping_charges}}
                                </td>
                            </tr>
                        @endif

                        <!-- Packing Charges -->
                        @if(!empty($receipt_details->packing_charge))
                            <tr class="color-555">
                                <td style="width:50%">
                                    {!! $receipt_details->packing_charge_label !!}
                                </td>
                                <td class="text-right">
                                    {{$receipt_details->packing_charge}}
                                </td>
                            </tr>
                        @endif

                        <!-- Discount -->
                        @if( !empty($receipt_details->discount) )
                            <tr class="color-555">
                                <td>
                                    {!! $receipt_details->discount_label !!}
                                </td>

                                <td class="text-right">
                                    (-) {{$receipt_details->discount}}
                                </td>
                            </tr>
                        @endif

                        @if( !empty($receipt_details->reward_point_label) )
                            <tr class="color-555">
                                <td>
                                    {!! $receipt_details->reward_point_label !!}
                                </td>

                                <td class="text-right">
                                    (-) {{$receipt_details->reward_point_amount}}
                                </td>
                            </tr>
                        @endif

                        @if(!empty($receipt_details->group_tax_details))
                            @foreach($receipt_details->group_tax_details as $key => $value)
                                <tr class="color-555">
                                    <td>
                                        {!! $key !!}
                                    </td>
                                    <td class="text-right">
                                        (+) {{$value}}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @if( !empty($receipt_details->tax) )
                                <tr class="color-555">
                                    <td>
                                        {!! $receipt_details->tax_label !!}
                                    </td>
                                    <td class="text-right">
                                        (+) {{$receipt_details->tax}}
                                    </td>
                                </tr>
                            @endif
                        @endif

                        @if( $receipt_details->round_off_amount > 0)
                            <tr class="color-555">
                                <td>
                                    {!! $receipt_details->round_off_label !!}
                                </td>
                                <td class="text-right">
                                    {{$receipt_details->round_off}}
                                </td>
                            </tr>
                        @endif

                        <!-- Total -->
{{--                        <tr style="border: 2px solid !important;">--}}
{{--                            <th style="color: #555 !important; font-size: 15px !important;" class="padding-10">--}}
{{--                                {!! $receipt_details->total_label !!}--}}
{{--                            </th>--}}
{{--                            <td class="text-right padding-10" style="color: #555 !important; font-size: 20px;">--}}
{{--                                {{$receipt_details->total}}--}}
{{--                            </td>--}}
{{--                        </tr>--}}
{{--                        @if(!empty($receipt_details->total_in_words))--}}
{{--                            <tr>--}}
{{--                                <td colspan="2" class="text-right">--}}
{{--                                    <small>({{$receipt_details->total_in_words}})</small>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                        @endif--}}

                        @if(!empty($receipt_details->total_due))
                        <tr>
                            <th style="background-color: #d2d6de !important; color: #000 !important; font-size: 15px !important;" class="padding-10">
                                {!! $receipt_details->total_due_label !!}
                            </th>
                            <td class="text-right padding-10" style="background-color: #d2d6de !important; color: #000 !important; font-size: 18px;">
                                {{$receipt_details->total_due}}
                            </td>
                        </tr>
                        @endif
                        <!-- Total Due-->
{{--                        @if(!empty($receipt_details->total_due))--}}
{{--                            <div class="text-right padding-10" style="background-color: #d2d6de !important; color: #000 !important; font-size: 20px;">--}}
{{--                                <span class="pull-left bg-light-blue-active">--}}
{{--                                    {!! $receipt_details->total_due_label !!}--}}
{{--                                </span>--}}
{{--                                {{$receipt_details->total_due}}--}}
{{--                            </div>--}}
{{--                        @endif--}}
                        </tbody>
                    </table>
                </div>
            </div>
            <table class="table table-bordered table-no-top-cell-border table-slim" style="width: 100%; border: 1px solid grey">
                    <thead>
                    <tr style="background-color: grey !important; padding: 5px; font-size: 18px !important; font-weight: bold;" class="table-no-side-cell-border table-no-top-cell-border text-center">
                        @if(!empty($receipt_details->total_paid))
                        <td style="background-color: #aaaaaa !important; padding: 5px" class="text-right" width="50%">
                            <span class="pull-right">{!! $receipt_details->total_paid_label !!}</span> &nbsp;&nbsp;&nbsp;
                            {{$receipt_details->total_paid}}
                        </td>
                        @endif
                        <td style="background-color: #aaaaaa !important; padding: 5px" class="text-right" width="50%">
                            <span class="pull-right">{!! $receipt_details->total_label !!}</span>
                            {{$receipt_details->total}}
                            @if(!empty($receipt_details->total_in_words))
                                <br>
                                <small>({{$receipt_details->total_in_words}})</small>
                            @endif
                        </td>
                    </tr>
                    </thead>
            </table>
            @if(!empty($receipt_details->additional_notes) || !empty($discount_by_variation))
            <div class="row color-555">
                <div class="col-xs-12">
                    <p>{!! nl2br($receipt_details->additional_notes) !!}</p>
                    @if(!empty($discount_by_variation))
                        <p><strong>Discount : {!! $discount_by_variation->name !!} applied</strong></p>
                    @endif
                </div>
            </div>
            @endif
            @if(!empty($receipt_details->footer_text))
                <div class="row color-555">
                    <div class="col-xs-12">
                        {!! $receipt_details->footer_text !!}
                    </div>
                </div>
            @endif
        </td>
    </tr>
    </tbody>
</table>