@extends('layouts.app')
@section('title', __( 'sale.list_pos'))

@section('content')

<section class="content no-print" style="padding: 15px 0;">

    <!-- Content Header (Page header) -->
    <section class="content-header f_content-header f_product_content-header" style="margin-bottom: 25px">
        <h1>@lang('sale.pos_sale')</h1>
        <div>
            @can('product.create')
                <a class="btn f_add-btn pull-right"
                    href="{{action([\App\Http\Controllers\SellPosController::class, 'create'])}}">
                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                <br><br>
            @endcan
        </div>
    </section>
    <div class="f_job_sheet_wrapper">
        @component('components.filters', ['title' => __('report.filters')])
        @include('sell.partials.sell_list_filters')
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'sale.list_pos')])
        {{-- @can('sell.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{action([\App\Http\Controllers\SellPosController::class, 'create'])}}">
                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                </div>
            @endslot
        @endcan --}}
        @can('sell.view')
            <input type="hidden" name="is_direct_sale" id="is_direct_sale" value="0">
            @include('sale_pos.partials.sales_table')
        @endcan
    @endcomponent
    </div>
</section>
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade register_details_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<!-- This will be printed -->
<!-- <section class="invoice print_section" id="receipt_section">
</section> -->


@stop

@section('javascript')
@include('sale_pos.partials.sale_table_javascript')
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection