@extends('layouts.app')
@section('title', __('barcode.print_labels'))

@section('content')

    <!-- Content Header (Page header) -->
    {{-- <section class="content-header">
<br>
    <h1>@lang('barcode.print_labels') @show_tooltip(__('tooltip.print_label'))</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section> --}}


    <!-- Main content -->
    <section class="content no-print">
        <section class="content-header f_content-header f_product_content-header f_lable_content-header">
            <div>
                <h1>@lang('barcode.print_labels') @show_tooltip(__('tooltip.print_label'))</h1>
            </div>
            <!-- <ol class="breadcrumb">
                       <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                       <li class="active">Here</li>
                      </ol> -->

        </section>
        {!! Form::open(['url' => '#', 'method' => 'post', 'id' => 'preview_setting_form', 'onsubmit' => 'return false']) !!}
        @component('components.widget', [
            'class' => 'box-primary lable_page_f_box',
            'title' => __('product.add_product_for_labels'),
        ])
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group" style="margin: 0">
                        <div class="input-group f_lable_input_group">
                            <span class="input-group-addon f_print_lable_input-group-addon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M13.4097 14.8822C11.7399 16.1799 9.63851 16.7922 7.53338 16.5942C5.42824 16.3963 3.47766 15.403 2.07881 13.8166C0.679961 12.2303 -0.0619809 10.1701 0.00405863 8.05565C0.0700982 5.94118 0.939153 3.9314 2.43427 2.43552C3.92939 0.939633 5.93814 0.0701341 8.05152 0.00406071C10.1649 -0.0620127 12.224 0.680308 13.8096 2.07987C15.3951 3.47944 16.3879 5.43102 16.5857 7.53723C16.7836 9.64345 16.1717 11.7459 14.8745 13.4166L19.6936 18.2201C20.1016 18.6267 20.1022 19.2872 19.695 19.6946C19.2878 20.1021 18.6273 20.1017 18.2204 19.6939L13.4201 14.8822H13.4097ZM8.31916 14.5495C9.13773 14.5495 9.94829 14.3882 10.7045 14.0748C11.4608 13.7614 12.148 13.302 12.7268 12.7229C13.3056 12.1438 13.7647 11.4563 14.078 10.6996C14.3913 9.94298 14.5525 9.13201 14.5525 8.31302C14.5525 7.49403 14.3913 6.68306 14.078 5.92641C13.7647 5.16976 13.3056 4.48225 12.7268 3.90314C12.148 3.32402 11.4608 2.86465 10.7045 2.55123C9.94829 2.23782 9.13773 2.07651 8.31916 2.07651C6.66598 2.07651 5.08051 2.73356 3.91153 3.90314C2.74256 5.07271 2.08583 6.659 2.08583 8.31302C2.08583 9.96705 2.74256 11.5533 3.91153 12.7229C5.08051 13.8925 6.66598 14.5495 8.31916 14.5495Z"
                                        fill="#8B83BA" />
                                </svg>

                            </span>
                            {!! Form::text('search_product', null, [
                                'class' => 'form-control f_lable_form-control',
                                'id' => 'search_product_for_label',
                                'placeholder' => __('lang_v1.enter_product_name_to_print_labels'),
                                'autofocus',
                            ]) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 ">
                    <table class="table table-bordered table-striped table-condensed" id="product_table">
                        <thead>
                            <tr class="f_tr-th">
                                <th class="f_lable_td">@lang('barcode.products')</th>
                                <th class="f_lable_td">@lang('barcode.no_of_labels')</th>
                                @if (request()->session()->get('business.enable_lot_number') == 1)
                                    <th>@lang('lang_v1.lot_number')</th>
                                @endif
                                @if (request()->session()->get('business.enable_product_expiry') == 1)
                                    <th>@lang('product.exp_date')</th>
                                @endif
                                <th class="f_lable_td">@lang('lang_v1.packing_date')</th>
                                <th class="f_lable_td">@lang('lang_v1.selling_price_group')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('labels.partials.show_table_rows', ['index' => 0])
                        </tbody>
                    </table>
                </div>
            </div>
        @endcomponent

        @component('components.widget', [
            'class' => 'box-primary lable_page_f_box',
            'title' => __('barcode.info_in_labels'),
        ])
            <div class="row">
                <div class="col-md-12">
                    <table class="table printed_table">
                        <tr>
                            <td class="addProduct_form">
                                <div class="checkbox f_checkbox">
                                    <input type="checkbox" class="" checked name="print[name]" value="1"
                                        id="print_name" style="height: auto !important;">
                                    <label for="print_name">@lang('barcode.print_name')</label>

                                </div>

                                <div class="input-group w-full m  w-full">
                                    {{-- <div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div> --}}
                                    <input type="text" class="form-control" name="print[name_size]" placeholder="Enter size"
                                        value="15">
                                </div>
                            </td>

                            <td class="addProduct_form">
                                <div class="checkbox f_checkbox">

                                    <input type="checkbox" checked name="print[variations]" value="1" id="print_variations"
                                        style="height: auto !important;">
                                    <label for="print_variations"> @lang('barcode.print_variations')</label>
                                </div>

                                <div class="input-group    w-full">
                                    {{-- <div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div> --}}
                                    <input type="text" class="form-control" name="print[variations_size]"
                                        placeholder="Enter  size" value="17">
                                </div>
                            </td>

                            <td class="addProduct_form"
                                style="flex-direction: column ; align-items: flex-start !important; gap: 0px !important">
                                <div class="checkbox f_checkbox">
                                    <input type="checkbox" checked name="print[price]" value="1" id="is_show_price"
                                        style="height: auto !important;">
                                    <label for="is_show_price">@lang('barcode.print_price')</label>
                                </div>

                                <div class="input-group    w-full">
                                    {{-- <div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div> --}}
                                    <input type="text" class="form-control" name="print[price_size]"
                                        placeholder="Enter  size" value="17">
                                </div>

                            </td>


                        </tr>

                        <tr>
                            <td class="addProduct_form">

                                <div class="w-full" id="price_type_div">
                                    <div class="form-group    w-full">
                                        {!! Form::label('print[price_type]', @trans('barcode.show_price') . ':') !!}
                                        <div class="input-group w-full">
                                            {{-- <span class="input-group-addon">
											<i class="fa fa-info"></i>
										</span> --}}
                                            {!! Form::select(
                                                'print[price_type]',
                                                ['inclusive' => __('product.inc_of_tax'), 'exclusive' => __('product.exc_of_tax')],
                                                'inclusive',
                                                ['class' => 'form-control'],
                                            ) !!}
                                        </div>
                                    </div>
                                </div>

                            </td>
                            <td class="addProduct_form ">
                                <div class="checkbox  f_checkbox">
                                    <input type="checkbox" checked name="print[business_name]" value="1"
                                        id="print_business_name" style="height: auto !important">
                                    <label for="print_business_name">@lang('barcode.print_business_name')</label>
                                </div>

                                <div class="input-group   w-full">
                                    {{-- <div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div> --}}
                                    <input type="text" class="form-control" name="print[business_name_size]"
                                        placeholder="Enter  size" value="20">
                                </div>
                            </td>

                            <td class="addProduct_form">
                                <div class="checkbox  f_checkbox">
                                    <input type="checkbox" checked name="print[packing_date]" value="1"
                                        id="print_packing_date" style="height: auto !important">
                                    <label for="print_packing_date">
                                        @lang('lang_v1.print_packing_date')
                                    </label>
                                </div>

                                <div class="input-group    w-full">
                                    {{-- <div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div> --}}
                                    <input type="text" class="form-control" name="print[packing_date_size]"
                                        placeholder="Enter  size" value="12">
                                </div>
                            </td>

                            <td class="addProduct_form">
                                @if (request()->session()->get('business.enable_lot_number') == 1)
                                    <div class="checkbox f_checkbox">
                                        <input type="checkbox" checked name="print[lot_number]" value="1"
                                            id="print_lot_number" style="height: auto !important">
                                        <label for="print_lot_number">
                                            @lang('lang_v1.print_lot_number')
                                        </label>
                                    </div>

                                    <div class="input-group    w-full">
                                        {{-- <div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div> --}}
                                        <input type="text" class="form-control" name="print[lot_number_size]"
                                            placeholder="Enter  size" value="12">
                                    </div>
                                @endif
                            </td>

                            <td class="addProduct_form">
                                @if (request()->session()->get('business.enable_product_expiry') == 1)
                                    <div class="checkbox f_checkbox">
                                        <input type="checkbox" checked name="print[exp_date]" value="1"
                                            id="print_exp_date" style="height: auto !important">
                                        <label for="print_exp_date">
                                            @lang('lang_v1.print_exp_date')
                                        </label>
                                    </div>

                                    <div class="input-group    w-full">
                                        {{-- <div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div> --}}
                                        <input type="text" class="form-control" name="print[exp_date_size]"
                                            placeholder="Enter  size" value="12">
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>





                <div class="col-sm-12">
                    <hr />
                </div>

                <div class="col-sm-4">
                    <div class="form-group addProduct_form printlables_barcode_setting">
                        {!! Form::label('price_type', @trans('barcode.barcode_setting') . ':') !!}
                        <div class="input-group">
                            {{-- <span class="input-group-addon">
							<i class="fa fa-cog"></i>
						</span> --}}
                            {!! Form::select('barcode_setting', $barcode_settings, !empty($default) ? $default->id : null, [
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>

                <div class="col-sm-4 col-sm-offset-4" style="padding: 20px 0">
                    <button type="button" id="labels_preview"
                        class="btn login-btn pull-right btn-flat btn-block">@lang('barcode.preview')</button>
                </div>
            </div>
        @endcomponent
        {!! Form::close() !!}

        <div class="col-sm-8 hide display_label_div">
            <h3 class="box-title">@lang('barcode.preview')</h3>
            <button type="button" class="col-sm-offset-2 btn btn-success btn-block" id="print_label">Print</button>
        </div>
        <div class="clearfix"></div>
    </section>

    <!-- Preview section-->
    <div id="preview_box">
    </div>

@stop
@section('javascript')
    <script src="{{ asset('js/labels.js?v=' . $asset_v) }}"></script>
@endsection

