
@extends('layouts.app')
@section('title', __( 'sale.zatca_invoice'))

@section('content')

    <section class="content no-print" style="padding: 15px 0;">

        <!-- Content Header (Page header) -->
        <section class="content-header f_content-header f_product_content-header" style="margin-bottom: 25px">
            <h1>@lang('sale.zatca_invoice')</h1>
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
{{--            @component('components.filters', ['title' => __('report.filters')])--}}
{{--                @include('sell.partials.sell_list_filters')--}}
{{--            @endcomponent--}}

            @component('components.widget', ['class' => 'box-primary', 'title' => __( 'sale.zatca_invoice')])
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
{{--                    @include('sale_pos.partials.sales_table')--}}
                        <table class="table table-bordered table-striped ajax_view" id="zatca_table">
                            <thead>
                            <tr class="f_tr-th">
                                <th>@lang('messages.action')</th>
                                <th>@lang('messages.date')</th>
                                <th>@lang('sale.invoice_no')</th>
                                <th>@lang('sale.customer_name')</th>
                                <th>@lang('lang_v1.contact_no')</th>
                                <th>@lang('sale.location')</th>
                                <th>@lang('sale.zatca_invoice_status')</th>
                                <th>@lang('sale.payment_status')</th>
                                <th>@lang('lang_v1.payment_method')</th>
                            </tr>
                            </thead>
{{--                            <tfoot>--}}
{{--                            <tr class="bg-gray font-17 footer-total text-center">--}}
{{--                                <td colspan="6"><strong>@lang('sale.total'):</strong></td>--}}
{{--                                <td class="footer_payment_status_count"></td>--}}
{{--                                <td class="payment_method_count"></td>--}}
{{--                                <td class="footer_sale_total"></td>--}}
{{--                                <td class="footer_total_paid"></td>--}}
{{--                                <td class="footer_total_remaining"></td>--}}
{{--                                <td class="footer_total_sell_return_due"></td>--}}
{{--                                <td colspan="2"></td>--}}
{{--                                <td class="service_type_count"></td>--}}
{{--                                <td colspan="7"></td>--}}
{{--                            </tr>--}}
{{--                            </tfoot>--}}
                        </table>
                @endcan
            @endcomponent
        </div>
    </section>
    <!-- /.content -->


@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready( function(){

//Date range as a button
            $('#sell_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function (start, end) {
                    $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                    zatca_table.ajax.reload();
                }
            );
            $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#sell_list_filter_date_range').val('');
                zatca_table.ajax.reload();
            });

            $(document).on('change', '#sell_list_filter_location_id, #sell_list_filter_customer_id, #sell_list_filter_payment_status, #created_by, #sales_cmsn_agnt, #service_staffs, #shipping_status',  function() {
                zatca_table.ajax.reload();
            });

            zatca_table = $('#zatca_table').DataTable({
                processing: true,
                serverSide: true,
                buttons: [],
                aaSorting: [[1, 'desc']],
                scrollY: "75vh",
                scrollX:        true,
                scrollCollapse: true,
                "ajax": {
                    "url": "/all-invoice-zatca",
                },
                columns: [
                    { data: 'action', name: 'action', orderable: false, "searchable": false},
                    { data: 'transaction_date', name: 'transaction_date'  },
                    { data: 'invoice_no', name: 'invoice_no'},
                    { data: 'business_location', name: 'bl.name'},
                    { data: 'payment_status', name: 'payment_status'},
                    { data: 'zatca_invoice_status', name: 'zatca_invoice_status'},
                    { data: 'payment_methods', orderable: false, "searchable": false},
                    { data: 'final_total', name: 'final_total'},
                    { data: 'total_paid', name: 'total_paid', "searchable": false},
                ],
                "fnDrawCallback": function (oSettings) {
                    __currency_convert_recursively($('#zatca_table'));
                },

                createdRow: function( row, data, dataIndex ) {
                    $( row ).find('td:eq(6)').attr('class', 'clickable_td');
                }
            });

            $('#only_subscriptions').on('ifChanged', function(event){
                zatca_table.ajax.reload();
            });
        });

    </script>
@endsection