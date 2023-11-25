@extends('layouts.app')

@section('title', __('accounting::lang.transactions'))

@section('content')

@include('accounting::layouts.nav')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'accounting::lang.transactions' )</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">

        <div class="col-xs-12 pos-tab-container">
            <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 pos-tab-menu">
                <div class="list-group">
                    <a href="#" class="list-group-item text-center active">@lang('sale.sale')</a>
                    <a href="#" class="list-group-item text-center">@lang('accounting::lang.sales_payments')</a>
                    <a href="#" class="list-group-item text-center">@lang('purchase.purchases')</a>
                    <a href="#" class="list-group-item text-center">@lang('accounting::lang.purchase_payments')</a>
                </div>
            </div>
            <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                @include('accounting::transactions.partials.sales')
            </div>
            <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                @include('accounting::transactions.partials.payments', ['id' => "sell_payment_table"])
            </div>
            <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                @include('accounting::transactions.partials.purchases',['id' => "purchases_table"])
            </div>
            <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                @include('accounting::transactions.partials.payments', ['id' => "purchase_payment_table"])
            </div>
        </div>
        
        </div>
    </div>

</section>
<!-- /.content -->
@stop

@section('javascript')
@include('accounting::accounting.common_js')
<script type="text/javascript">

          $('#sell_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#sell_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                        sell_table.ajax.reload();
                }
            );
        $('#sell_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#sell_filter_date_range').val('');
            sell_table.ajax.reload();
        });
    $(document).ready( function(){
        sell_table = $('#sell_table').DataTable({
            processing: true,
            serverSide: true,
            aaSorting: [[1, 'desc']],
            "ajax": {
                "url": "/accounting/transactions/?type=sell&datatable=sell",
                "data": function ( d ) {
                    if($('#sell_filter_date_range').val()) {
                        var start = $('#sell_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        var end = $('#sell_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        d.start_date = start;
                        d.end_date = end;
                    }
                    d.is_direct_sale = 1; 
                    d.customer_id = $('#sell_list_filter_customer_id').val();
                    d.payment_method = $('#sell_list_payment_method').val();
                    d.payment_status = $('#sell_list_filter_payment_status').val();
                    d.created_by = $('#created_by').val();
                    d.sales_cmsn_agnt = $('#sales_cmsn_agnt').val();
                    d.service_staffs = $('#service_staffs').val();

                    if($('#shipping_status').length) {
                        d.shipping_status = $('#shipping_status').val();
                    }

                    if($('#sell_list_filter_source').length) {
                        d.source = $('#sell_list_filter_source').val();
                    }

                    if($('#only_subscriptions').is(':checked')) {
                        d.only_subscriptions = 1;
                    }
                    
                    d = __datatable_ajax_callback(d);
                }
            },
            scrollY:        "75vh",
            scrollX:        true,
            scrollCollapse: true,
            columns: [
                { data: 'action', name: 'action', orderable: false, "searchable": false},
                { data: 'transaction_date', name: 'transaction_date'  },
                { data: 'invoice_no', name: 'invoice_no'},
                { data: 'conatct_name', name: 'conatct_name'},
                { data: 'mobile', name: 'contacts.mobile'},
                { data: 'business_location', name: 'bl.name'},
                { data: 'payment_status', name: 'payment_status'},
                { data: 'payment_methods', orderable: false, "searchable": false},
                { data: 'final_total', name: 'final_total'},
                { data: 'total_paid', name: 'total_paid', "searchable": false},
                { data: 'added_by', name: 'u.first_name'},
                { data: 'additional_notes', name: 'additional_notes'},
                { data: 'staff_note', name: 'staff_note'}
            ],
            "fnDrawCallback": function (oSettings) {
                __currency_convert_recursively($('#sell_table'));
            }
        });

    $(document).on('change', '#sell_list_filter_customer_id,#created_by,#sell_list_payment_method,#sell_list_filter_payment_status',
    function() {
        sell_table.ajax.reload();
    });

    $(document).on('change', '#sell_payment_table_created_by,#sell_payment_table_payment_method',
    function() {
        sell_payment_table.ajax.reload();
    }); 

        $('#sell_payment_table_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#sell_payment_table_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                        sell_payment_table.ajax.reload();
                }
            );
        $('#sell_payment_table_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#sell_payment_table_date_range').val('');
            sell_payment_table.ajax.reload();
        });

        sell_payment_table = $('#sell_payment_table').DataTable({
                            processing: true,
                            serverSide: true,
                            "ajax": {
                                "url": "/accounting/transactions/?transaction_type=sell&datatable=payment",
                                "data": function ( d ) { 
                                    if($('#sell_payment_table_date_range').val()) {
                                        var start = $('#sell_payment_table_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                                        var end = $('#sell_payment_table_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                                        d.start_date = start;
                                        d.end_date = end;
                                    }
                                 
                                    d.payment_method = $('#sell_payment_table_payment_method').val(); 
                                    d.created_by = $('#sell_payment_table_created_by').val();
                                }
                            },
                            columnDefs:[{
                                "targets": 0,
                                "orderable": false,
                                "searchable": false
                            }],
                            columns: [
                                {data: 'action', name: 'action'},
                                {data: 'paid_on', name: 'paid_on'},
                                {data: 'payment_ref_no', name: 'payment_ref_no'},
                                {data: 'transaction_number', name: 'transaction_number'},
                                {data: 'amount', name: 'amount'},
                                {data: 'type', name: 'T.type'},
                                {data: 'details', name: 'details', "searchable": false},
                            ],
                            "fnDrawCallback": function (oSettings) {
                                __currency_convert_recursively($('#sell_payment_table'));
                            }
                        });

     $(document).on('change', '#purchase_payment_table_created_by,#purchase_payment_table_payment_method',
    function() {
        purchase_payment_table.ajax.reload();
    }); 

        $('#purchase_payment_table_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#purchase_payment_table_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                        purchase_payment_table.ajax.reload();
                }
            );
        $('#purchase_payment_table_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#purchase_payment_table_date_range').val('');
            purchase_payment_table.ajax.reload();
        });

        purchase_payment_table = $('#purchase_payment_table').DataTable({
                            processing: true,
                            serverSide: true,
                            "ajax": {
                                "url": "/accounting/transactions/?transaction_type=purchase&datatable=payment",
                                "data": function ( d ) {
                                    if($('#purchase_payment_table_date_range').val()) {
                                        var start = $('#purchase_payment_table_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                                        var end = $('#purchase_payment_table_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                                        d.start_date = start;
                                        d.end_date = end;
                                    }
                                 
                                    d.payment_method = $('#purchase_payment_table_payment_method').val(); 
                                    d.created_by = $('#purchase_payment_table_created_by').val();
                                }
                            },
                            columnDefs:[{
                                "targets": 0,
                                "orderable": false,
                                "searchable": false
                            }],
                            columns: [
                                {data: 'action', name: 'action'},
                                {data: 'paid_on', name: 'paid_on'},
                                {data: 'payment_ref_no', name: 'payment_ref_no'},
                                {data: 'transaction_number', name: 'transaction_number'},
                                {data: 'amount', name: 'amount'},
                                {data: 'type', name: 'T.type'},
                                {data: 'details', name: 'details', "searchable": false},
                            ],
                            "fnDrawCallback": function (oSettings) {
                                __currency_convert_recursively($('#sell_payment_table'));
                            }
                        });

        //Purchase table

        
     $(document).on('change', '#purchases_table_created_by,#purchases_table_payment_method',
    function() {
        purchase_table.ajax.reload();
    }); 

        $('#purchases_table_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#purchases_table_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                        purchase_table.ajax.reload();
                }
            );
        $('#purchases_table_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#purchases_table_date_range').val('');
            purchase_table.ajax.reload();
        });

        purchase_table = $('#purchase_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/accounting/transactions/?datatable=purchase',
                data: function(d) {
                             if($('#purchases_table_date_range').val()) {
                                        var start = $('#purchases_table_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                                        var end = $('#purchases_table_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                                        d.start_date = start;
                                        d.end_date = end;
                                    }
                                 
                                    d.payment_method = $('#purchases_table_payment_method').val(); 
                                    d.created_by = $('#purchases_table_created_by').val();
                                

                    d = __datatable_ajax_callback(d);
                },
            },
            aaSorting: [[1, 'desc']],
            columns: [
                { data: 'action', name: 'action', orderable: false, searchable: false },
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'ref_no', name: 'ref_no' },
                { data: 'location_name', name: 'BS.name' },
                { data: 'name', name: 'contacts.name' },
                { data: 'status', name: 'status' },
                { data: 'payment_status', name: 'payment_status' },
                { data: 'final_total', name: 'final_total' },
                { data: 'payment_due', name: 'payment_due', orderable: false, searchable: false },
                { data: 'added_by', name: 'u.first_name' },
            ],
            fnDrawCallback: function(oSettings) {
                __currency_convert_recursively($('#purchase_table'));
            }
        });

        $(document).on('submit', "form#save_accounting_map", function(e){
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();
            transaction_type = $('#transaction_type').val();

            $.ajax({
                method: 'POST',
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        $('div.view_modal').modal('hide');
                        toastr.success(result.msg);
                        if(transaction_type == 'sell'){
                            sell_table.ajax.reload();
                        } else if(transaction_type == 'sell_payment'){
                            sell_payment_table.ajax.reload();
                        } else if(transaction_type == 'purchase'){
                            purchase_table.ajax.reload();
                        } else if(transaction_type == 'purchase_payment'){
                            purchase_payment_table.ajax.reload();
                        }
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });


        });
    });
</script>
@stop