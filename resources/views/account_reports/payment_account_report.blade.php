@extends('layouts.app')
@section('title', __('account.payment_account_report'))

@section('content')

{{-- <!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{ __('account.payment_account_report')}}</h1>
</section> --}}

<!-- Main content -->
<section class="content" style="background: #fff;border-radius: 10px">
    <section class="content-header " style="border-bottom: .5px solid #ed143d;padding-bottom: 20px;margin: 0 20px; ">
    <h1>{{ __('account.payment_account_report')}}</h1>
</section>
    <div class="row">
        <div class="col-md-12">
            <div class="box f_box" id="accordion">
              <div class="box-header with-border">
                <h3 class="box-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapseFilter">
                    <i class="fa fa-filter" aria-hidden="true"></i> @lang('report.filters')
                  </a>
                </h3>
              </div>
              <div id="collapseFilter" class="panel-collapse active collapse in" aria-expanded="true">
                <div class="box-body">
                    <div class="col-md-3">
                        <div class="form-group addProduct_form">
                            {!! Form::label('account_id', __('account.account') . ':') !!}
                            {!! Form::select('account_id', $accounts, null, ['class' => 'form-control select2']); !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group addProduct_form">
                            {!! Form::label('date_filter', __('report.date_range') . ':') !!}
                            {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'date_filter', 'readonly']); !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group addProduct_form">
                            {!! Form::label('payment_types', __('lang_v1.payment_method') . ':') !!}
                            <div class="input-group w-full">
                                {{-- <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span> --}}
                                {!! Form::select('payment_types', $payment_types, null, [
                                    'class' => 'form-control select2',
                                    'id' => 'payment_types',
                                    'placeholder' => __('messages.all'),
                                    'required',
                                ]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group f_product_form-group">
                            {!! Form::label('payment_status',  __('purchase.payment_status') . ':') !!}
                            {!! Form::select('payment_status', ['paid' => __('lang_v1.paid'), 'due' => __('lang_v1.due'), 'partial' => __('lang_v1.partial'), 'overdue' => __('lang_v1.overdue')], null, ['class' => 'form-control select2', 'id'=> 'payment_status', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                        </div>
                    </div>
                </div>
              </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="box f_box">
                <div class="box-body">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="payment_account_report">
                        <thead>
                            <tr class="f_tr-th">
                                <th>@lang('messages.date')</th>
                                <th>@lang('account.payment_ref_no')</th>
                                <th>@lang('account.invoice_ref_no')</th>
                                <th>@lang('sale.amount')</th>
                                <th>@lang('lang_v1.payment_method')</th>
                                <th>@lang('sale.payment_status')</th>
                                <th>@lang('account.account')</th>
                                <th>@lang( 'lang_v1.description' )</th>
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

@endsection

@section('javascript')
    
    <script type="text/javascript">
        $(document).ready(function(){
            if($('#date_filter').length == 1){
                $('#date_filter').daterangepicker(
                    dateRangeSettings,
                    function (start, end) {
                        $('#date_filter').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                        payment_account_report.ajax.reload();
                    }
                );

                $('#date_filter').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    payment_account_report.ajax.reload();
                });
            }

            payment_account_report = $('#payment_account_report').DataTable({
                            processing: true,
                            serverSide: true,
                            buttons:[],
                            "ajax": {
                                "url": "{{action([\App\Http\Controllers\AccountReportsController::class, 'paymentAccountReport'])}}",
                                "data": function ( d ) {
                                    d.account_id = $('#account_id').val();
                                    d.payment_types = $('#payment_types').val();
                                    d.payment_status = $('#payment_status').val();
                                    var start_date = '';
                                    var endDate = '';
                                    if($('#date_filter').val()){
                                        var start_date = $('#date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                                        var endDate = $('#date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
                                    }
                                    d.start_date = start_date;
                                    d.end_date = endDate;
                                }
                            },
                            columnDefs:[{
                                "targets": 7,
                                "orderable": false,
                                "searchable": false
                            }],
                            columns: [
                                {data: 'paid_on', name: 'paid_on'},
                                {data: 'payment_ref_no', name: 'payment_ref_no'},
                                {data: 'transaction_number', name: 'transaction_number'},
                                {data: 'amount', name: 'amount'},
                                {data: 'method', name: 'method'},
                                {data: 'payment_status', name: 'T.payment_status'},
                                {data: 'account', name: 'account'},
                                {data: 'details', name: 'details', "searchable": false},
                                {data: 'action', name: 'action'}
                            ],
                            "fnDrawCallback": function (oSettings) {
                                __currency_convert_recursively($('#payment_account_report'));
                            }
                        });
            
            $('select#account_id, #date_filter, #payment_types, #payment_status').change( function(){
                payment_account_report.ajax.reload();
            });

        })

        $(document).on('submit', 'form#link_account_form', function(e){
            e.preventDefault();
            var data = $(this).serialize();

            $.ajax({
                method: $(this).attr("method"),
                url: $(this).attr("action"),
                dataType: "json",
                data: data,
                success: function(result){
                    if(result.success === true){
                        $('div.view_modal').modal('hide');
                        toastr.success(result.msg);
                        payment_account_report.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });
    </script>
@endsection