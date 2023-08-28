@extends('layouts.app')
@section('title', __('product.import_products'))

@section('content')

<!-- Content Header (Page header) -->
{{-- <section class="content-header">
    <h1>@lang('product.import_products')
    </h1>
</section> --}}

<!-- Main content -->
<section class="content" style="padding:  15px 0">
    <section class="content-header f_content-header f_product_content-header f_import_content-header" style="margin-bottom: 25px">
        <h1>@lang('product.import_products')
        </h1>
        <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
            <li class="active">Here</li>
        </ol> -->
        <div class="row">
            
                {{-- <a href="{{ asset('files/import_products_csv_template.xls') }}" class="btn f-download-btn " download>
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
                </svg>@lang('lang_v1.download_template_file')</a> --}}
    
                <a class="btn f-download-btn pull-right margin-left-10"
                href="{{ asset('files/import_products_csv_template.xls') }}">
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
                    @if(!empty($notification['msg']))
                        {{$notification['msg']}}
                    @elseif(session('notification.msg'))
                        {{ session('notification.msg') }}
                    @endif
                </div>
            </div>  
        </div>     
    @endif
    
  <div class="f_import_instruction_file_warpper">
    
    <div class="row f_import_instruction"  >
        <div class="col-sm-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.instructions')])
                <strong>@lang('lang_v1.instruction_line1')</strong><br>
                    @lang('lang_v1.instruction_line2')
                    <br><br>
                <table class="table table-striped">
                    <tr class='f_tr-th'>
                        <th width="20%">@lang('lang_v1.col_no')</th>
                        <th>@lang('lang_v1.col_name')</th>
                        <th>@lang('lang_v1.instruction')</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>@lang('product.product_name') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>@lang('lang_v1.name_ins')</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>@lang('product.brand') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.brand_ins') <br><small class="text-muted">(@lang('lang_v1.brand_ins2'))</small></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>@lang('product.unit') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>@lang('lang_v1.unit_ins')</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>@lang('product.category') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.category_ins') <br><small class="text-muted">(@lang('lang_v1.category_ins2'))</small></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>@lang('product.sub_category') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.sub_category_ins') <br><small class="text-muted">({!! __('lang_v1.sub_category_ins2') !!})</small></td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>@lang('product.sku') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.sku_ins')</td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>@lang('product.barcode_type') <small class="text-muted">(@lang('lang_v1.optional'), @lang('lang_v1.default'): C128)</small></td>
                        <td>@lang('lang_v1.barcode_type_ins') <br>
                            <strong>@lang('lang_v1.barcode_type_ins2'): C128, C39, EAN-13, EAN-8, UPC-A, UPC-E, ITF-14</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>@lang('product.manage_stock') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>@lang('lang_v1.manage_stock_ins')<br>
                            <strong>1 = @lang('messages.yes')<br>
                            0 = @lang('messages.no')</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>@lang('product.alert_quantity') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('product.alert_quantity')</td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td>@lang('product.expires_in') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.expires_in_ins')</td>
                    </tr>
                    <tr>
                        <td>11</td>
                        <td>@lang('lang_v1.expire_period_unit') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.expire_period_unit_ins')<br>
                            <strong>@lang('lang_v1.available_options'): days, months</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>12</td>
                        <td>@lang('product.applicable_tax') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.applicable_tax_ins') {!! __('lang_v1.applicable_tax_help') !!}</td>
                    </tr>
                    <tr>
                        <td>13</td>
                        <td>@lang('product.selling_price_tax_type') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>@lang('product.selling_price_tax_type') <br>
                            <strong>@lang('lang_v1.available_options'): inclusive, exclusive</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>14</td>
                        <td>@lang('product.product_type') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>@lang('product.product_type') <br>
                            <strong>@lang('lang_v1.available_options'): single, variable</strong></td>
                    </tr>
                    <tr>
                        <td>15</td>
                        <td>@lang('product.variation_name') <small class="text-muted">(@lang('lang_v1.variation_name_ins'))</small></td>
                        <td>@lang('lang_v1.variation_name_ins2')</td>
                    </tr>
                    <tr>
                        <td>16</td>
                        <td>@lang('product.variation_values') <small class="text-muted">(@lang('lang_v1.variation_values_ins'))</small></td>
                        <td>{!! __('lang_v1.variation_values_ins2') !!}</td>
                    </tr>
                    <tr>
                        <td>17</td>
                        <td>@lang('lang_v1.variation_sku') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>{!! __('lang_v1.variation_sku_ins') !!}</td>
                    </tr>
                    <tr>
                        <td>18</td>
                        <td> @lang('lang_v1.purchase_price_inc_tax')<br><small class="text-muted">(@lang('lang_v1.purchase_price_inc_tax_ins1'))</small></td>
                        <td>{!! __('lang_v1.purchase_price_inc_tax_ins2') !!}</td>
                    </tr>
                    <tr>
                        <td>19</td>
                        <td>@lang('lang_v1.purchase_price_exc_tax')  <br><small class="text-muted">(@lang('lang_v1.purchase_price_exc_tax_ins1'))</small></td>
                        <td>{!! __('lang_v1.purchase_price_exc_tax_ins2') !!}</td>
                    </tr>
                    <tr>
                        <td>20</td>
                        <td>@lang('lang_v1.profit_margin') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.profit_margin_ins')<br>
                            <small class="text-muted">{!! __('lang_v1.profit_margin_ins1') !!}</small></td>
                    </tr>
                    <tr>
                        <td>21</td>
                        <td>@lang('lang_v1.selling_price') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.selling_price_ins')<br>
                         <small class="text-muted">{!! __('lang_v1.selling_price_ins1') !!}</small></td>
                    </tr>
                    <tr>
                        <td>22</td>
                        <td>@lang('lang_v1.opening_stock') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.opening_stock_ins') {!! __('lang_v1.opening_stock_help_text') !!}<br>
                        </td>
                    </tr>
                    <tr>
                        <td>23</td>
                        <td>@lang('lang_v1.opening_stock_location') <small class="text-muted">(@lang('lang_v1.optional')) <br>@lang('lang_v1.location_ins')</small></td>
                        <td>@lang('lang_v1.location_ins1')<br>
                        </td>
                    </tr>
                    <tr>
                        <td>24</td>
                        <td>@lang('lang_v1.expiry_date') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>{!! __('lang_v1.expiry_date_ins') !!}<br>
                        </td>
                    </tr>
                    <tr>
                        <td>25</td>
                        <td>@lang('lang_v1.enable_imei_or_sr_no') <small class="text-muted">(@lang('lang_v1.optional'), @lang('lang_v1.default'): 0)</small></td>
                        <td><strong>1 = @lang('messages.yes')<br>
                            0 = @lang('messages.no')</strong><br>
                        </td>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('lang_v1.weight') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.optional')<br>
                        </td>
                    </tr>
                    <tr>
                        <td>27</td>
                        <td>@lang('lang_v1.rack') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>{!! __('lang_v1.rack_help_text') !!}</td>
                    </tr>
                    <tr>
                        <td>28</td>
                        <td>@lang('lang_v1.row') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>{!! __('lang_v1.row_help_text') !!}</td>
                    </tr>
                    <tr>
                        <td>29</td>
                        <td>@lang('lang_v1.position') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>{!! __('lang_v1.position_help_text') !!}</td>
                    </tr>
                    <tr>
                        <td>30</td>
                        <td>@lang('lang_v1.image') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>{!! __('lang_v1.image_help_text', ['path' => 'public/uploads/'.config('constants.product_img_path')]) !!} <br><br>
                            {{__('lang_v1.img_url_help_text')}} 
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>31</td>
                        <td>@lang('lang_v1.product_description') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>32</td>
                        <td>@lang('lang_v1.product_custom_field1') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>33</td>
                        <td>@lang('lang_v1.product_custom_field2') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                    </tr>
                    <tr>
                        <td>34</td>
                        <td>@lang('lang_v1.product_custom_field3') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>35</td>
                        <td>@lang('lang_v1.product_custom_field4') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                    </tr>
                    <tr>
                        <td>36</td>
                        <td>@lang('lang_v1.not_for_selling') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td><strong>1 = @lang('messages.yes')<br>
                            0 = @lang('messages.no')</strong><br>
                        </td>
                    </tr>
                    <tr>
                        <td>37</td>
                        <td>@lang('lang_v1.product_locations') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.product_locations_ins')
                        </td>
                    </tr>

                </table>
            @endcomponent
        </div>
    </div>
    <div class="row f_import_file">
        <div class="col-sm-12">
            @component('components.widget', ['class' => 'box-primary'])
                {!! Form::open(['url' => action([\App\Http\Controllers\ImportProductsController::class, 'store']), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
                    <div class="row">
                        <div class="col-sm-12">
                        <div class="col-sm-12">
                            <div class="form-group">
                                {!! Form::label('name', __( 'product.file_to_import' ) . ':') !!}
                                {!! Form::file('products_csv', ['accept'=> '.xls, .xlsx, .csv', 'required' => 'required']); !!}
                              </div>
                        </div>
                        <div class="col-sm-12">
                  
                            <button type="submit" class="btn f_add-btn">@lang('messages.submit')</button>
                        </div>
                        </div>
                    </div>

                {!! Form::close() !!}
                <br><br>
                {{-- <div class="row">
                    <div class="col-sm-4">
                        <a href="{{ asset('files/import_products_csv_template.xls') }}" class="btn btn-success" download><i class="fa fa-download"></i> @lang('lang_v1.download_template_file')</a>
                    </div>
                </div> --}}
            @endcomponent
        </div>
    </div>
  </div>
</section>
<!-- /.content -->

@endsection