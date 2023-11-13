<div class="row">
    <div class="f_sell_list_filters" style="">
        @if(empty($only) || in_array('sell_list_filter_location_id', $only))
<div class="col-md-3">
    <div class="form-group f_product_form-group">
        {!! Form::label('sell_list_filter_location_id',  __('purchase.business_location') . ':') !!}

        {!! Form::select('sell_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all') ]); !!}
    </div>
</div>
@endif
@if(empty($only) || in_array('sell_list_filter_customer_id', $only))
<div class="col-md-3">
    <div class="form-group f_product_form-group">
        {!! Form::label('sell_list_filter_customer_id',  __('contact.customer') . ':') !!}
        {!! Form::select('sell_list_filter_customer_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
    </div>
</div>
@endif
@if(empty($only) || in_array('sell_list_filter_payment_status', $only))
<div class="col-md-3">
    <div class="form-group f_product_form-group">
        {!! Form::label('sell_list_filter_payment_status',  __('purchase.payment_status') . ':') !!}
        {!! Form::select('sell_list_filter_payment_status', ['paid' => __('lang_v1.paid'), 'due' => __('lang_v1.due'), 'partial' => __('lang_v1.partial'), 'overdue' => __('lang_v1.overdue')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
    </div>
</div>
@endif

            <div class="col-md-3">
                <div class="form-group addProduct_form">
                    {!! Form::label('payment_method', __('lang_v1.payment_method') . ':') !!}
                    <div class="input-group w-full">
                        {{-- <span class="input-group-addon">
                        <i class="fas fa-money-bill-alt"></i>
                    </span> --}}
                        {!! Form::select('payment_method', $payment_types, null, [
                            'class' => 'form-control select2',
                            'placeholder' => __('messages.all'),
                            'id' => 'payment_method',
                            'required',
                        ]) !!}
                    </div>
                </div>
            </div>
@if(empty($only) || in_array('sell_list_filter_date_range', $only))
<div class="col-md-3">
    <div class="form-group f_product_form-group">
        {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
        {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
    </div>
</div>
@endif
@if((empty($only) || in_array('created_by', $only)) && !empty($sales_representative))
<div class="col-md-3">
    <div class="form-group f_product_form-group">
        {!! Form::label('created_by',  __('report.user') . ':') !!}
        {!! Form::select('created_by', $sales_representative, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
    </div>
</div>
@endif
@if(empty($only) || in_array('sales_cmsn_agnt', $only))
@if(!empty($is_cmsn_agent_enabled))
    <div class="col-md-3">
        <div class="form-group f_product_form-group">
            {!! Form::label('sales_cmsn_agnt',  __('lang_v1.sales_commission_agent') . ':') !!}
            {!! Form::select('sales_cmsn_agnt', $commission_agents, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
        </div>
    </div>
@endif
@endif
@if(empty($only) || in_array('service_staffs', $only))
@if(!empty($service_staffs))
    <div class="col-md-3">
        <div class="form-group f_product_form-group">
            {!! Form::label('service_staffs', __('restaurant.service_staff') . ':') !!}
            {!! Form::select('service_staffs', $service_staffs, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
        </div>
    </div>
@endif
@endif
@if(!empty($shipping_statuses))
    <div class="col-md-3">
        <div class="form-group f_product_form-group">
            {!! Form::label('shipping_status', __('lang_v1.shipping_status') . ':') !!}
            {!! Form::select('shipping_status', $shipping_statuses, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
        </div>
    </div>
@endif
@if(empty($only) || in_array('only_subscriptions', $only))
<div class="col-md-3">
    <div class="form-group f_product_form-group">
        <div class="checkbox">
            <label>
                <br>
              {!! Form::checkbox('only_subscriptions', 1, false, 
              [ 'class' => 'input-icheck', 'id' => 'only_subscriptions']); !!} {{ __('lang_v1.subscriptions') }}
            </label>
        </div>
    </div>
</div>
@endif
    </div>
</div>