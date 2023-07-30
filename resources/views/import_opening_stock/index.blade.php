@extends('layouts.app')
@section('title', __('lang_v1.import_opening_stock'))

@section('content')
    <br />
    <!-- Content Header (Page header) -->
    {{-- <section class="content-header">
    <h1>@lang('lang_v1.import_opening_stock')</h1>
</section> --}}

    <!-- Main content -->
    <section class="content" style="padding: 15px 0;">
        <section class="content-header f_content-header f_product_content-header f_import_content-header" style="margin-bottom: 25px">
            <h1>@lang('lang_v1.import_opening_stock')</h1>
            <!-- <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                    <li class="active">Here</li>
                </ol> -->
            <div class="row">


                <a class="btn f-download-btn pull-right margin-left-10"
                    href="{{ asset('files/import_opening_stock_csv_template.xls') }}">
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

                    @lang('lang_v1.download_template_file')</a>

            </div>
        </section>
        @if (session('notification') || !empty($notification))
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        @if (!empty($notification['msg']))
                            {{ $notification['msg'] }}
                        @elseif(session('notification.msg'))
                            {{ session('notification.msg') }}
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="f_import_instruction_file_warpper">
            <div class="row f_import_instruction" >
                <div class="col-sm-12">
                    @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.instructions')])
                        <strong>@lang('lang_v1.instruction_line1')</strong><br>@lang('lang_v1.instruction_line2')
                        <br><br>
                        <table class="table table-striped">
                            <tr class='f_tr-th'>
                                <th>@lang('lang_v1.col_no')</th>
                                <th>@lang('lang_v1.col_name')</th>
                                <th>@lang('lang_v1.instruction')</th>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>@lang('product.sku')<small class="text-muted">(@lang('lang_v1.required'))</small></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>@lang('business.location') <small class="text-muted">(@lang('lang_v1.optional'))
                                        <br>@lang('lang_v1.location_ins')</small>
                                </td>
                                <td>@lang('lang_v1.location_ins1')<br>
                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>@lang('lang_v1.quantity') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>@lang('purchase.unit_cost_before_tax') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td>@lang('lang_v1.lot_number') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>6</td>
                                <td>@lang('lang_v1.expiry_date') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                                <td>{!! __('lang_v1.expiry_date_in_business_date_format') !!} <br /> <b>{{ $date_format }}</b>, @lang('lang_v1.type'): <b>text</b>,
                                    @lang('lang_v1.example'): <b>{{ @format_date('today') }}</b></td>
                            </tr>
                        </table>
                    @endcomponent
                </div>
            </div>
            <div class="row f_import_file" >
                <div class="col-sm-12">
                    @component('components.widget', ['class' => 'box-primary'])
                        {!! Form::open([
                            'url' => action([\App\Http\Controllers\ImportOpeningStockController::class, 'store']),
                            'method' => 'post',
                            'enctype' => 'multipart/form-data',
                        ]) !!}
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        {!! Form::label('name', __('product.file_to_import') . ':') !!}
                                        @show_tooltip(__('lang_v1.tooltip_import_opening_stock'))
                                        {!! Form::file('products_csv', ['accept' => '.xls', 'required' => 'required']) !!}
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="col-sm-4">

                            <button type="submit" class="btn f_add-btn ">@lang('messages.submit')</button>
                        </div>
                        {!! Form::close() !!}
                        <br><br>
                        {{-- <div class="row">
                        <div class="col-sm-4">
                            <a href="{{ asset('files/import_opening_stock_csv_template.xls') }}" class="btn btn-success"
                                download><i class="fa fa-download"></i> @lang('lang_v1.download_template_file')</a>
                        </div>
                    </div> --}}
                    @endcomponent
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->

@endsection
