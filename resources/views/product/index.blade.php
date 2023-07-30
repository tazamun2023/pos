@extends('layouts.app')
@section('title', __('sale.products'))

@section('content')

    <!-- Content Header (Page header) -->
    {{-- <section class="content-header">
    <h1>@lang('sale.products')
        <small>@lang('lang_v1.manage_products')</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section> --}}


    <!-- Main content -->
    <section class="content" style="padding: 15px 0;">
        <!-- Content Header (Page header) -->
        <section class="content-header f_content-header f_product_content-header" style="margin-bottom: 25px">
            <h1>@lang('sale.products')
                <small>@lang('lang_v1.manage_products')</small>
            </h1>
            <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
            <li class="active">Here</li>
        </ol> -->
            <div>
                @can('product.create')
                    <a class="btn f_add-btn pull-right"
                        href="{{ action([\App\Http\Controllers\ProductController::class, 'create']) }}">
                        <i class="fa fa-plus"></i> @lang('messages.add')</a>
                    <br><br>
                @endcan
            </div>
        </section>
        @can('product.view')
            <div class="row">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <div class="f-tab">
                            <ul class="nav nav-tabs f_payroll_nav">
                                <li class="active">
                                    <a href="#product_list_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes"
                                            aria-hidden="true"></i> @lang('lang_v1.all_products')</a>
                                </li>
                                @can('stock_report.view')
                                    <li>
                                        <a href="#product_stock_report" data-toggle="tab" aria-expanded="true"><i
                                                class="fa fa-hourglass-half" aria-hidden="true"></i> @lang('report.stock_report')</a>
                                    </li>
                                @endcan
                            </ul>
                            <div>
                                @if ($is_admin)
                                    <a class="btn f-download-btn pull-right margin-left-10"
                                        href="{{ action([\App\Http\Controllers\ProductController::class, 'downloadExcel']) }}">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_199_1764)">
                                                <path
                                                    d="M10.8334 10.8334V15.4876L12.3567 13.9642L13.5359 15.1434L10 18.6784L6.4642 15.1434L7.64337 13.9642L9.1667 15.4876V10.8334H10.8334ZM10 1.66675C11.4309 1.66682 12.8118 2.19268 13.8802 3.14435C14.9486 4.09602 15.6301 5.40713 15.795 6.82841C16.8319 7.11118 17.7365 7.74933 18.3506 8.63135C18.9647 9.51337 19.2493 10.5832 19.1547 11.6537C19.0601 12.7243 18.5923 13.7276 17.833 14.4883C17.0737 15.2489 16.0713 15.7185 15.0009 15.8151V14.1367C15.3843 14.082 15.7531 13.9514 16.0855 13.7526C16.418 13.5537 16.7075 13.2907 16.9372 12.9788C17.1669 12.6668 17.3321 12.3123 17.4233 11.9358C17.5144 11.5593 17.5297 11.1684 17.4681 10.786C17.4066 10.4035 17.2695 10.0372 17.0648 9.70829C16.8601 9.37941 16.592 9.09459 16.276 8.87047C15.9601 8.64635 15.6027 8.48741 15.2246 8.40292C14.8466 8.31844 14.4555 8.31011 14.0742 8.37841C14.2047 7.77082 14.1977 7.14172 14.0535 6.53721C13.9094 5.93269 13.6319 5.36807 13.2414 4.88469C12.8508 4.40131 12.357 4.01142 11.7963 3.74358C11.2355 3.47573 10.6219 3.33672 10.0004 3.33672C9.379 3.33672 8.76541 3.47573 8.20464 3.74358C7.64387 4.01142 7.15011 4.40131 6.75954 4.88469C6.36896 5.36807 6.09146 5.93269 5.94735 6.53721C5.80325 7.14172 5.79619 7.77082 5.9267 8.37841C5.16641 8.23564 4.38055 8.40073 3.74199 8.83738C3.10343 9.27403 2.66448 9.94646 2.5217 10.7067C2.37893 11.467 2.54402 12.2529 2.98067 12.8915C3.41732 13.53 4.08975 13.969 4.85003 14.1117L5.00003 14.1367V15.8151C3.92958 15.7187 2.92704 15.2492 2.16765 14.4886C1.40825 13.728 0.940306 12.7247 0.845596 11.6541C0.750885 10.5835 1.03543 9.51365 1.64951 8.63156C2.26358 7.74947 3.16812 7.11124 4.20503 6.82841C4.36979 5.40706 5.05121 4.09584 6.11968 3.14414C7.18816 2.19243 8.56916 1.66663 10 1.66675Z"
                                                    fill="white" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_199_1764">
                                                    <rect width="20" height="20" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>

                                        @lang('lang_v1.download_excel')</a>
                                @endif
                            </div>
                        </div>

                        <div class="tab-content">
                            <div class="row">
                                <div class="col-md-12">
                                    @component('components.filters', ['title' => __('report.filters')])
                                        <div class="col-md-3">
                                            <div class="form-group f_product_form-group  ">
                                                {!! Form::label('type', __('product.product_type') . ':') !!}
                                                {!! Form::select(
                                                    'type',
                                                    ['single' => __('lang_v1.single'), 'variable' => __('lang_v1.variable'), 'combo' => __('lang_v1.combo')],
                                                    null,
                                                    [
                                                        'class' => 'form-control select2',
                                                        'style' => 'width:100%',
                                                        'id' => 'product_list_filter_type',
                                                        'placeholder' => __('lang_v1.all'),
                                                    ],
                                                ) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group f_product_form-group">
                                                {!! Form::label('category_id', __('product.category') . ':') !!}
                                                {!! Form::select('category_id', $categories, null, [
                                                    'class' => 'form-control select2',
                                                    'style' => 'width:100%',
                                                    'id' => 'product_list_filter_category_id',
                                                    'placeholder' => __('lang_v1.all'),
                                                ]) !!}
                                            </div>
                                        </div>

                                        <div class="col-md-3 f_product_form-group">
                                            <div class="form-group">
                                                {!! Form::label('unit_id', __('product.unit') . ':') !!}
                                                {!! Form::select('unit_id', $units, null, [
                                                    'class' => 'form-control select2',
                                                    'style' => 'width:100%',
                                                    'id' => 'product_list_filter_unit_id',
                                                    'placeholder' => __('lang_v1.all'),
                                                ]) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-3 f_product_form-group">
                                            <div class="form-group">
                                                {!! Form::label('tax_id', __('product.tax') . ':') !!}
                                                {!! Form::select('tax_id', $taxes, null, [
                                                    'class' => 'form-control select2',
                                                    'style' => 'width:100%',
                                                    'id' => 'product_list_filter_tax_id',
                                                    'placeholder' => __('lang_v1.all'),
                                                ]) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group f_product_form-group">
                                                {!! Form::label('brand_id', __('product.brand') . ':') !!}
                                                {!! Form::select('brand_id', $brands, null, [
                                                    'class' => 'form-control select2',
                                                    'style' => 'width:100%',
                                                    'id' => 'product_list_filter_brand_id',
                                                    'placeholder' => __('lang_v1.all'),
                                                ]) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-3" id="location_filter">
                                            <div class="form-group f_product_form-group">
                                                {!! Form::label('location_id', __('purchase.business_location') . ':') !!}
                                                {!! Form::select('location_id', $business_locations, null, [
                                                    'class' => 'form-control select2',
                                                    'style' => 'width:100%',
                                                    'placeholder' => __('lang_v1.all'),
                                                ]) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <br>
                                            <div class="form-group f_product_form-group">
                                                {!! Form::select(
                                                    'active_state',
                                                    ['active' => __('business.is_active'), 'inactive' => __('lang_v1.inactive')],
                                                    null,
                                                    [
                                                        'class' => 'form-control select2',
                                                        'style' => 'width:100%',
                                                        'id' => 'active_state',
                                                        'placeholder' => __('lang_v1.all'),
                                                    ],
                                                ) !!}
                                            </div>
                                        </div>

                                        <!-- include module filter -->
                                        @if (!empty($pos_module_data))
                                            @foreach ($pos_module_data as $key => $value)
                                                @if (!empty($value['view_path']))
                                                    @includeIf($value['view_path'], [
                                                        'view_data' => $value['view_data'],
                                                    ])
                                                @endif
                                            @endforeach
                                        @endif

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <br>
                                                <label>
                                                    {!! Form::checkbox('not_for_selling', 1, false, ['class' => 'input-icheck', 'id' => 'not_for_selling']) !!} <strong>@lang('lang_v1.not_for_selling')</strong>
                                                </label>
                                            </div>
                                        </div>
                                        @if ($is_woocommerce)
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <br>
                                                    <label>
                                                        {!! Form::checkbox('woocommerce_enabled', 1, false, ['class' => 'input-icheck', 'id' => 'woocommerce_enabled']) !!} {{ __('lang_v1.woocommerce_enabled') }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    @endcomponent
                                </div>
                            </div>
                            <div class="tab-pane active" id="product_list_tab" style="padding: 0 15px">
                                {{-- @if ($is_admin)
                            <a class="btn btn-success pull-right margin-left-10" href="{{action([\App\Http\Controllers\ProductController::class, 'downloadExcel'])}}"><i class="fa fa-download"></i> @lang('lang_v1.download_excel')</a>
                        @endif
                        @can('product.create')                            
                            <a class="btn btn-primary pull-right" href="{{action([\App\Http\Controllers\ProductController::class, 'create'])}}">
                                        <i class="fa fa-plus"></i> @lang('messages.add')</a>
                            <br><br>
                        @endcan --}}
                                @include('product.partials.product_list')
                            </div>
                            @can('stock_report.view')
                                <div class="tab-pane" id="product_stock_report" style="padding: 0 15px">
                                    @include('report.partials.stock_report_table')
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @endcan
        <input type="hidden" id="is_rack_enabled" value="{{ $rack_enabled }}">

        <div class="modal fade product_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade" id="view_product_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade" id="opening_stock_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>

        @if ($is_woocommerce)
            @include('product.partials.toggle_woocommerce_sync_modal')
        @endif
        @include('product.partials.edit_product_location_modal')

    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            product_table = $('#product_table').DataTable({
                buttons: [],

                processing: true,
                serverSide: true,
                aaSorting: [
                    [3, 'asc']
                ],
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                "ajax": {
                    "url": "/products",
                    "data": function(d) {
                        d.type = $('#product_list_filter_type').val();
                        d.category_id = $('#product_list_filter_category_id').val();
                        d.brand_id = $('#product_list_filter_brand_id').val();
                        d.unit_id = $('#product_list_filter_unit_id').val();
                        d.tax_id = $('#product_list_filter_tax_id').val();
                        d.active_state = $('#active_state').val();
                        d.not_for_selling = $('#not_for_selling').is(':checked');
                        d.location_id = $('#location_id').val();
                        if ($('#repair_model_id').length == 1) {
                            d.repair_model_id = $('#repair_model_id').val();
                        }

                        if ($('#woocommerce_enabled').length == 1 && $('#woocommerce_enabled').is(
                                ':checked')) {
                            d.woocommerce_enabled = 1;
                        }

                        d = __datatable_ajax_callback(d);
                    }
                },
                columnDefs: [{
                    "targets": [0, 1, 2],
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                        data: 'mass_delete'
                    },
                    {
                        data: 'image',
                        name: 'products.image'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                    {
                        data: 'product',
                        name: 'products.name'
                    },
                    {
                        data: 'product_locations',
                        name: 'product_locations'
                    },
                    @can('view_purchase_price')
                        {
                            data: 'purchase_price',
                            name: 'max_purchase_price',
                            searchable: false
                        },
                    @endcan
                    @can('access_default_selling_price')
                        {
                            data: 'selling_price',
                            name: 'max_price',
                            searchable: false
                        },
                    @endcan {
                        data: 'current_stock',
                        searchable: false
                    },
                    {
                        data: 'type',
                        name: 'products.type'
                    },
                    {
                        data: 'category',
                        name: 'c1.name'
                    },
                    {
                        data: 'brand',
                        name: 'brands.name'
                    },
                    {
                        data: 'tax',
                        name: 'tax_rates.name',
                        searchable: false
                    },
                    {
                        data: 'sku',
                        name: 'products.sku'
                    },
                    {
                        data: 'product_custom_field1',
                        name: 'products.product_custom_field1'
                    },
                    {
                        data: 'product_custom_field2',
                        name: 'products.product_custom_field2'
                    },
                    {
                        data: 'product_custom_field3',
                        name: 'products.product_custom_field3'
                    },
                    {
                        data: 'product_custom_field4',
                        name: 'products.product_custom_field4'
                    }

                ],
                createdRow: function(row, data, dataIndex) {
                    if ($('input#is_rack_enabled').val() == 1) {
                        var target_col = 0;
                        @can('product.delete')
                            target_col = 1;
                        @endcan
                        $(row).find('td:eq(' + target_col + ') div').prepend(
                            '<i style="margin:auto;" class="fa fa-plus-circle text-success cursor-pointer no-print rack-details" title="' +
                            LANG.details + '"></i>&nbsp;&nbsp;');
                    }
                    $(row).find('td:eq(0)').attr('class', 'selectable_td');
                },
                fnDrawCallback: function(oSettings) {
                    __currency_convert_recursively($('#product_table'));
                },
            });
            // Array to track the ids of the details displayed rows
            var detailRows = [];

            $('#product_table tbody').on('click', 'tr i.rack-details', function() {
                var i = $(this);
                var tr = $(this).closest('tr');
                var row = product_table.row(tr);
                var idx = $.inArray(tr.attr('id'), detailRows);

                if (row.child.isShown()) {
                    i.addClass('fa-plus-circle text-success');
                    i.removeClass('fa-minus-circle text-danger');

                    row.child.hide();

                    // Remove from the 'open' array
                    detailRows.splice(idx, 1);
                } else {
                    i.removeClass('fa-plus-circle text-success');
                    i.addClass('fa-minus-circle text-danger');

                    row.child(get_product_details(row.data())).show();

                    // Add to the 'open' array
                    if (idx === -1) {
                        detailRows.push(tr.attr('id'));
                    }
                }
            });

            $('#opening_stock_modal').on('hidden.bs.modal', function(e) {
                product_table.ajax.reload();
            });

            $('table#product_table tbody').on('click', 'a.delete-product', function(e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).attr('href');
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    product_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });

            $(document).on('click', '#delete-selected', function(e) {
                e.preventDefault();
                var selected_rows = getSelectedRows();

                if (selected_rows.length > 0) {
                    $('input#selected_rows').val(selected_rows);
                    swal({
                        title: LANG.sure,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            $('form#mass_delete_form').submit();
                        }
                    });
                } else {
                    $('input#selected_rows').val('');
                    swal('@lang('lang_v1.no_row_selected')');
                }
            });

            $(document).on('click', '#deactivate-selected', function(e) {
                e.preventDefault();
                var selected_rows = getSelectedRows();

                if (selected_rows.length > 0) {
                    $('input#selected_products').val(selected_rows);
                    swal({
                        title: LANG.sure,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            var form = $('form#mass_deactivate_form')

                            var data = form.serialize();
                            $.ajax({
                                method: form.attr('method'),
                                url: form.attr('action'),
                                dataType: 'json',
                                data: data,
                                success: function(result) {
                                    if (result.success == true) {
                                        toastr.success(result.msg);
                                        product_table.ajax.reload();
                                        form
                                            .find('#selected_products')
                                            .val('');
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                },
                            });
                        }
                    });
                } else {
                    $('input#selected_products').val('');
                    swal('@lang('lang_v1.no_row_selected')');
                }
            })

            $(document).on('click', '#edit-selected', function(e) {
                e.preventDefault();
                var selected_rows = getSelectedRows();

                if (selected_rows.length > 0) {
                    $('input#selected_products_for_edit').val(selected_rows);
                    $('form#bulk_edit_form').submit();
                } else {
                    $('input#selected_products').val('');
                    swal('@lang('lang_v1.no_row_selected')');
                }
            })

            $('table#product_table tbody').on('click', 'a.activate-product', function(e) {
                e.preventDefault();
                var href = $(this).attr('href');
                $.ajax({
                    method: "get",
                    url: href,
                    dataType: "json",
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            product_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });

            $(document).on('change',
                '#product_list_filter_type, #product_list_filter_category_id, #product_list_filter_brand_id, #product_list_filter_unit_id, #product_list_filter_tax_id, #location_id, #active_state, #repair_model_id',
                function() {
                    if ($("#product_list_tab").hasClass('active')) {
                        product_table.ajax.reload();
                    }

                    if ($("#product_stock_report").hasClass('active')) {
                        stock_report_table.ajax.reload();
                    }
                });

            $(document).on('ifChanged', '#not_for_selling, #woocommerce_enabled', function() {
                if ($("#product_list_tab").hasClass('active')) {
                    product_table.ajax.reload();
                }

                if ($("#product_stock_report").hasClass('active')) {
                    stock_report_table.ajax.reload();
                }
            });

            $('#product_location').select2({
                dropdownParent: $('#product_location').closest('.modal')
            });

            @if ($is_woocommerce)
                $(document).on('click', '.toggle_woocomerce_sync', function(e) {
                    e.preventDefault();
                    var selected_rows = getSelectedRows();
                    if (selected_rows.length > 0) {
                        $('#woocommerce_sync_modal').modal('show');
                        $("input#woocommerce_products_sync").val(selected_rows);
                    } else {
                        $('input#selected_products').val('');
                        swal('@lang('lang_v1.no_row_selected')');
                    }
                });

                $(document).on('submit', 'form#toggle_woocommerce_sync_form', function(e) {
                    e.preventDefault();
                    var url = $('form#toggle_woocommerce_sync_form').attr('action');
                    var method = $('form#toggle_woocommerce_sync_form').attr('method');
                    var data = $('form#toggle_woocommerce_sync_form').serialize();
                    var ladda = Ladda.create(document.querySelector('.ladda-button'));
                    ladda.start();
                    $.ajax({
                        method: method,
                        dataType: "json",
                        url: url,
                        data: data,
                        success: function(result) {
                            ladda.stop();
                            if (result.success) {
                                $("input#woocommerce_products_sync").val('');
                                $('#woocommerce_sync_modal').modal('hide');
                                toastr.success(result.msg);
                                product_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                });
            @endif
        });

        $(document).on('shown.bs.modal', 'div.view_product_modal, div.view_modal, #view_product_modal',
            function() {
                var div = $(this).find('#view_product_stock_details');
                if (div.length) {
                    $.ajax({
                        url: "{{ action([\App\Http\Controllers\ReportController::class, 'getStockReport']) }}" +
                            '?for=view_product&product_id=' + div.data('product_id'),
                        dataType: 'html',
                        success: function(result) {
                            div.html(result);
                            __currency_convert_recursively(div);
                        },
                    });
                }
                __currency_convert_recursively($(this));
            });
        var data_table_initailized = false;
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            if ($(e.target).attr('href') == '#product_stock_report') {
                if (!data_table_initailized) {
                    //Stock report table
                    var stock_report_cols = [{
                            data: 'action',
                            name: 'action',
                            searchable: false,
                            orderable: false
                        },
                        {
                            data: 'sku',
                            name: 'variations.sub_sku'
                        },
                        {
                            data: 'product',
                            name: 'p.name'
                        },
                        {
                            data: 'variation',
                            name: 'variation'
                        },
                        {
                            data: 'category_name',
                            name: 'c.name'
                        },
                        {
                            data: 'location_name',
                            name: 'l.name'
                        },
                        {
                            data: 'unit_price',
                            name: 'variations.sell_price_inc_tax'
                        },
                        {
                            data: 'stock',
                            name: 'stock',
                            searchable: false
                        },
                    ];
                    if ($('th.stock_price').length) {
                        stock_report_cols.push({
                            data: 'stock_price',
                            name: 'stock_price',
                            searchable: false
                        });
                        stock_report_cols.push({
                            data: 'stock_value_by_sale_price',
                            name: 'stock_value_by_sale_price',
                            searchable: false,
                            orderable: false
                        });
                        stock_report_cols.push({
                            data: 'potential_profit',
                            name: 'potential_profit',
                            searchable: false,
                            orderable: false
                        });
                    }

                    stock_report_cols.push({
                        data: 'total_sold',
                        name: 'total_sold',
                        searchable: false
                    });
                    stock_report_cols.push({
                        data: 'total_transfered',
                        name: 'total_transfered',
                        searchable: false
                    });
                    stock_report_cols.push({
                        data: 'total_adjusted',
                        name: 'total_adjusted',
                        searchable: false
                    });
                    stock_report_cols.push({
                        data: 'product_custom_field1',
                        name: 'p.product_custom_field1'
                    });
                    stock_report_cols.push({
                        data: 'product_custom_field2',
                        name: 'p.product_custom_field2'
                    });
                    stock_report_cols.push({
                        data: 'product_custom_field3',
                        name: 'p.product_custom_field3'
                    });
                    stock_report_cols.push({
                        data: 'product_custom_field4',
                        name: 'p.product_custom_field4'
                    });

                    if ($('th.current_stock_mfg').length) {
                        stock_report_cols.push({
                            data: 'total_mfg_stock',
                            name: 'total_mfg_stock',
                            searchable: false
                        });
                    }
                    stock_report_table = $('#stock_report_table').DataTable({
                        order: [
                            [1, 'asc']
                        ],
                        buttons:[],
                        processing: true,
                        serverSide: true,
                        scrollY: "75vh",
                        scrollX: true,
                        scrollCollapse: true,
                        ajax: {
                            url: '/reports/stock-report',
                            data: function(d) {
                                d.location_id = $('#location_id').val();
                                d.category_id = $('#product_list_filter_category_id').val();
                                d.brand_id = $('#product_list_filter_brand_id').val();
                                d.unit_id = $('#product_list_filter_unit_id').val();
                                d.type = $('#product_list_filter_type').val();
                                d.active_state = $('#active_state').val();
                                d.not_for_selling = $('#not_for_selling').is(':checked');
                                if ($('#repair_model_id').length == 1) {
                                    d.repair_model_id = $('#repair_model_id').val();
                                }
                            }
                        },
                        columns: stock_report_cols,
                        fnDrawCallback: function(oSettings) {
                            __currency_convert_recursively($('#stock_report_table'));
                        },
                        "footerCallback": function(row, data, start, end, display) {
                            var footer_total_stock = 0;
                            var footer_total_sold = 0;
                            var footer_total_transfered = 0;
                            var total_adjusted = 0;
                            var total_stock_price = 0;
                            var footer_stock_value_by_sale_price = 0;
                            var total_potential_profit = 0;
                            var footer_total_mfg_stock = 0;
                            for (var r in data) {
                                footer_total_stock += $(data[r].stock).data('orig-value') ?
                                    parseFloat($(data[r].stock).data('orig-value')) : 0;

                                footer_total_sold += $(data[r].total_sold).data('orig-value') ?
                                    parseFloat($(data[r].total_sold).data('orig-value')) : 0;

                                footer_total_transfered += $(data[r].total_transfered).data(
                                        'orig-value') ?
                                    parseFloat($(data[r].total_transfered).data('orig-value')) : 0;

                                total_adjusted += $(data[r].total_adjusted).data('orig-value') ?
                                    parseFloat($(data[r].total_adjusted).data('orig-value')) : 0;

                                total_stock_price += $(data[r].stock_price).data('orig-value') ?
                                    parseFloat($(data[r].stock_price).data('orig-value')) : 0;

                                footer_stock_value_by_sale_price += $(data[r].stock_value_by_sale_price)
                                    .data('orig-value') ?
                                    parseFloat($(data[r].stock_value_by_sale_price).data(
                                    'orig-value')) : 0;

                                total_potential_profit += $(data[r].potential_profit).data(
                                    'orig-value') ?
                                    parseFloat($(data[r].potential_profit).data('orig-value')) : 0;

                                footer_total_mfg_stock += $(data[r].total_mfg_stock).data(
                                    'orig-value') ?
                                    parseFloat($(data[r].total_mfg_stock).data('orig-value')) : 0;
                            }

                            $('.footer_total_stock').html(__currency_trans_from_en(footer_total_stock,
                                false));
                            $('.footer_total_stock_price').html(__currency_trans_from_en(
                                total_stock_price));
                            $('.footer_total_sold').html(__currency_trans_from_en(footer_total_sold,
                                false));
                            $('.footer_total_transfered').html(__currency_trans_from_en(
                                footer_total_transfered, false));
                            $('.footer_total_adjusted').html(__currency_trans_from_en(total_adjusted,
                                false));
                            $('.footer_stock_value_by_sale_price').html(__currency_trans_from_en(
                                footer_stock_value_by_sale_price));
                            $('.footer_potential_profit').html(__currency_trans_from_en(
                                total_potential_profit));
                            if ($('th.current_stock_mfg').length) {
                                $('.footer_total_mfg_stock').html(__currency_trans_from_en(
                                    footer_total_mfg_stock, false));
                            }
                        },
                    });
                    data_table_initailized = true;
                } else {
                    stock_report_table.ajax.reload();
                }
            } else {
                product_table.ajax.reload();
            }
        });

        $(document).on('click', '.update_product_location', function(e) {
            e.preventDefault();
            var selected_rows = getSelectedRows();

            if (selected_rows.length > 0) {
                $('input#selected_products').val(selected_rows);
                var type = $(this).data('type');
                var modal = $('#edit_product_location_modal');
                if (type == 'add') {
                    modal.find('.remove_from_location_title').addClass('hide');
                    modal.find('.add_to_location_title').removeClass('hide');
                } else if (type == 'remove') {
                    modal.find('.add_to_location_title').addClass('hide');
                    modal.find('.remove_from_location_title').removeClass('hide');
                }

                modal.modal('show');
                modal.find('#product_location').select2({
                    dropdownParent: modal
                });
                modal.find('#product_location').val('').change();
                modal.find('#update_type').val(type);
                modal.find('#products_to_update_location').val(selected_rows);
            } else {
                $('input#selected_products').val('');
                swal('@lang('lang_v1.no_row_selected')');
            }
        });

        $(document).on('submit', 'form#edit_product_location_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();

            $.ajax({
                method: $(this).attr('method'),
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                beforeSend: function(xhr) {
                    __disable_submit_button(form.find('button[type="submit"]'));
                },
                success: function(result) {
                    if (result.success == true) {
                        $('div#edit_product_location_modal').modal('hide');
                        toastr.success(result.msg);
                        product_table.ajax.reload();
                        $('form#edit_product_location_form')
                            .find('button[type="submit"]')
                            .attr('disabled', false);
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });
    </script>
@endsection
