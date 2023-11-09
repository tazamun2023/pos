@extends('layouts.app')
@section('title', __('product.add_new_product'))

@section('content')



    <!-- Main content -->
    <section class="content addproduct_content" style="padding: 15px 0;">
        <!-- Content Header (Page header) -->
        <section class="content-header addproduct_content_header">
            <h1>@lang('product.add_new_product')</h1>
            <!-- <ol class="breadcrumb">
                              <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                              <li class="active">Here</li>
                          </ol> -->
        </section>
        @php
            $form_class = empty($duplicate_product) ? 'create' : '';
            $is_image_required = !empty($common_settings['is_product_image_required']);
        @endphp
        {!! Form::open([
            'url' => action([\App\Http\Controllers\ProductController::class, 'store']),
            'method' => 'post',
            'id' => 'product_add_form',
            'class' => 'product_form ' . $form_class,
            'files' => true,
        ]) !!}
        @component('components.widget', ['class' => 'box-primary'])
            <div class="product_items">
                <div class="product_left_item">
                    <h2 class="product_item_header">Product Details</h2>
                    <div class="product_details_wrapper">
                        <div class="product_detiles_items">
                            <div class="form-group addProduct_form">
                                {!! Form::label('name', __('product.product_name') . ':*') !!}
                                {!! Form::text('name', !empty($duplicate_product->name) ? $duplicate_product->name : null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('product.product_name'),
                                ]) !!}
                            </div>
                        </div>

                        <div class="product_detiles_items">
                            <div class="form-group addProduct_form">
                                {!! Form::label('unit_id', __('product.unit') . ':*') !!}
                                <div class="input-group">
                                    {!! Form::select(
                                        'unit_id',
                                        $units,
                                        !empty($duplicate_product->unit_id) ? $duplicate_product->unit_id : session('business.default_unit'),
                                        ['class' => 'form-control select2', 'required'],
                                    ) !!}
                                    <span class="input-group-btn">
                                        <button type="button" @if (!auth()->user()->can('unit.create')) disabled @endif
                                            class="btn btn-default bg-white btn-flat btn-modal"
                                            data-href="{{ action([\App\Http\Controllers\UnitController::class, 'create'], ['quick_add' => true]) }}"
                                            title="@lang('unit.add_unit')" data-container=".view_modal"><i
                                                class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="product_detiles_items @if (!session('business.enable_sub_units')) hide @endif">
                            <div class="form-group addProduct_form">
                                {!! Form::label('sub_unit_ids', __('lang_v1.related_sub_units') . ':') !!} @show_tooltip(__('lang_v1.sub_units_tooltip'))

                                {!! Form::select(
                                    'sub_unit_ids[]',
                                    [],
                                    !empty($duplicate_product->sub_unit_ids) ? $duplicate_product->sub_unit_ids : null,
                                    ['class' => 'form-control select2', 'multiple', 'id' => 'sub_unit_ids'],
                                ) !!}
                            </div>
                        </div>
                        @if (!empty($common_settings['enable_secondary_unit']))
                            <div class="product_detiles_items">
                                <div class="form-group addProduct_form">
                                    {!! Form::label('secondary_unit_id', __('lang_v1.secondary_unit') . ':') !!} @show_tooltip(__('lang_v1.secondary_unit_help'))
                                    {!! Form::select(
                                        'secondary_unit_id',
                                        $units,
                                        !empty($duplicate_product->secondary_unit_id) ? $duplicate_product->secondary_unit_id : null,
                                        ['class' => 'form-control select2'],
                                    ) !!}
                                </div>
                            </div>
                        @endif


                        <div class="product_detiles_items @if (!session('business.enable_brand')) hide @endif">
                            <div class="form-group addProduct_form">
                                {!! Form::label('brand_id', __('product.brand') . ':') !!}
                                <div class="input-group">
                                    {!! Form::select(
                                        'brand_id',
                                        $brands,
                                        !empty($duplicate_product->brand_id) ? $duplicate_product->brand_id : null,
                                        ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'],
                                    ) !!}
                                    <span class="input-group-btn">
                                        <button type="button" @if (!auth()->user()->can('brand.create')) disabled @endif
                                            class="btn btn-default bg-white btn-flat btn-modal"
                                            data-href="{{ action([\App\Http\Controllers\BrandController::class, 'create'], ['quick_add' => true]) }}"
                                            title="@lang('brand.add_brand')" data-container=".view_modal"><i
                                                class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="product_detiles_items @if (!session('business.enable_category')) hide @endif">
                            <div class="input-group addProduct_form">
                                {!! Form::label('category_id', __('product.category') . ':') !!}
                                {!! Form::select(
                                    'category_id',
                                    $categories,
                                    !empty($duplicate_product->category_id) ? $duplicate_product->category_id : null,
                                    ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'],
                                ) !!}

                                <span class="input-group-btn">
                                    <button type="button" @if (!auth()->user()->can('brand.create')) disabled @endif
                                    class="btn btn-default bg-white btn-flat btn-modal"
                                            data-href="{{ action([\App\Http\Controllers\TaxonomyController::class, 'create'], ['quick_add' => true]) }}"
                                            title="@lang('brand.add_brand')" data-container=".view_modal"><i
                                                class="fa fa-plus-circle text-primary fa-lg"></i></button>

                                    </span>
                            </div>
                        </div>

                        <div class="product_detiles_items @if (!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
                            <div class="form-group addProduct_form">
                                {!! Form::label('sub_category_id', __('product.sub_category') . ':') !!}
                                {!! Form::select(
                                    'sub_category_id',
                                    $sub_categories,
                                    !empty($duplicate_product->sub_category_id) ? $duplicate_product->sub_category_id : null,
                                    ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'],
                                ) !!}
                            </div>
                        </div>

                        <div class="product_detiles_items">
                            <div class="form-group addProduct_form">
                                {!! Form::label('barcode_type', __('product.barcode_type') . ':*') !!}
                                {!! Form::select(
                                    'barcode_type',
                                    $barcode_types,
                                    !empty($duplicate_product->barcode_type) ? $duplicate_product->barcode_type : $barcode_default,
                                    ['class' => 'form-control select2', 'required'],
                                ) !!}
                            </div>
                        </div>

                        <div class="product_detiles_items">
                            <div class="form-group addProduct_form">
                                {!! Form::label('sku', __('product.sku') . ':') !!} @show_tooltip(__('tooltip.sku'))
                                {!! Form::text('sku', null, ['class' => 'form-control', 'placeholder' => __('product.sku')]) !!}
                            </div>
                        </div>

                        @php
                            $default_location = null;
                            if (count($business_locations) == 1) {
                                $default_location = array_key_first($business_locations->toArray());
                            }
                        @endphp
                        <div class="product_detiles_items">
                            <div class="form-group addProduct_form">
                                {!! Form::label('product_locations', __('business.business_locations') . ':') !!} @show_tooltip(__('lang_v1.product_location_help'))
                                {!! Form::select('product_locations[]', $business_locations, $default_location, [
                                    'class' => 'form-control select2',
                                    'multiple',
                                    'id' => 'product_locations',
                                ]) !!}
                            </div>
                        </div>

                        <div class="product_detiles_items @if (!empty($duplicate_product) && $duplicate_product->enable_stock == 0) hide @endif"
                            id="alert_quantity_div">
                            <div class="form-group addProduct_form">
                                {!! Form::label('alert_quantity', __('product.alert_quantity') . ':') !!} @show_tooltip(__('tooltip.alert_quantity'))
                                {!! Form::text(
                                    'alert_quantity',
                                    !empty($duplicate_product->alert_quantity) ? @format_quantity($duplicate_product->alert_quantity) : null,
                                    ['class' => 'form-control input_number', 'placeholder' => __('product.alert_quantity'), 'min' => '0'],
                                ) !!}
                            </div>
                        </div>

                        @if (!empty($common_settings['enable_product_warranty']))
                            <div class="product_detiles_items">
                                <div class="form-group addProduct_form">
                                    {!! Form::label('warranty_id', __('lang_v1.warranty') . ':') !!}
                                    {!! Form::select('warranty_id', $warranties, null, [
                                        'class' => 'form-control select2',
                                        'placeholder' => __('messages.please_select'),
                                    ]) !!}
                                </div>
                            </div>
                        @endif

                        <div class="product_detiles_items">
                            <div class="form-group addProduct_form">
                                <br>
                                <label>
                                    {!! Form::checkbox('enable_stock', 1, !empty($duplicate_product) ? $duplicate_product->enable_stock : true, [
                                        'class' => 'input-icheck',
                                        'id' => 'enable_stock',
                                    ]) !!} <strong>@lang('product.manage_stock')</strong>
                                </label>@show_tooltip(__('tooltip.enable_stock')) <p class="help-block"><i>@lang('product.enable_stock_help')</i>
                                </p>
                            </div>
                        </div>

                        <div class="product_detiles_items">
                            <div class="form-group addProduct_form ">
                                {!! Form::label('product_description', __('lang_v1.product_description') . ':') !!}
                                {!! Form::textarea(
                                    'product_description',
                                    !empty($duplicate_product->product_description) ? $duplicate_product->product_description : null,
                                    ['class' => 'form-control'],
                                ) !!}
                            </div>
                        </div>






                    </div>
                </div>
                <div class="product_right_item">
                    <div class="product_image">
                        <h2 class="product_item_header">Product Image</h2>
                        <div class="">
                            <div class="product_image_wrapper">
                                {{--                             <div class="form-group product_image_form"> --}}
                                {{--                                 <svg width="45" height="40" viewBox="0 0 45 40" fill="none" xmlns="http://www.w3.org/2000/svg"> --}}
                                {{--                                     <path d="M22.5 21.172L30.986 29.656L28.156 32.486L24.5 28.83V40H20.5V28.826L16.844 32.486L14.014 29.656L22.5 21.172ZM22.5 4.4432e-08C25.934 0.00016354 29.2482 1.26223 31.8124 3.54624C34.3767 5.83025 36.0122 8.97693 36.408 12.388C38.8966 13.0666 41.0675 14.5982 42.5414 16.7151C44.0152 18.8319 44.6983 21.3994 44.4713 23.9688C44.2442 26.5382 43.1214 28.9461 41.2992 30.7716C39.4769 32.5972 37.071 33.7243 34.502 33.956V29.928C35.4224 29.7966 36.3073 29.4831 37.1052 29.0059C37.9031 28.5288 38.5979 27.8974 39.1492 27.1488C39.7004 26.4002 40.097 25.5493 40.3158 24.6457C40.5346 23.7421 40.5712 22.804 40.4235 21.8861C40.2758 20.9683 39.9467 20.089 39.4555 19.2997C38.9642 18.5104 38.3207 17.8268 37.5624 17.2889C36.8042 16.751 35.9464 16.3696 35.0391 16.1668C34.1317 15.9641 33.1931 15.9441 32.278 16.108C32.5913 14.6498 32.5743 13.1399 32.2285 11.6891C31.8826 10.2383 31.2166 8.88317 30.2792 7.72306C29.3418 6.56295 28.1568 5.62721 26.811 4.98439C25.4651 4.34157 23.9925 4.00794 22.501 4.00794C21.0095 4.00794 19.5369 4.34157 18.1911 4.98439C16.8452 5.62721 15.6602 6.56295 14.7228 7.72306C13.7855 8.88317 13.1194 10.2383 12.7736 11.6891C12.4277 13.1399 12.4108 14.6498 12.724 16.108C10.8993 15.7653 9.01327 16.1616 7.48072 17.2095C5.94817 18.2575 4.89469 19.8713 4.55203 21.696C4.20937 23.5207 4.6056 25.4068 5.65356 26.9393C6.70151 28.4719 8.31534 29.5253 10.14 29.868L10.5 29.928V33.956C7.93093 33.7247 5.52484 32.5978 3.7023 30.7724C1.87976 28.947 0.756686 26.5391 0.529381 23.9697C0.302075 21.4002 0.984991 18.8326 2.45877 16.7155C3.93255 14.5985 6.10345 13.0668 8.59203 12.388C8.98744 8.97675 10.6228 5.82982 13.1872 3.54573C15.7515 1.26164 19.0659 -0.000273405 22.5 4.4432e-08Z" fill="#767676"/> --}}
                                {{--                                 </svg> --}}
                                {{--                                {!! Form::label('image', __('lang_v1.product_image') . ':') !!} --}}
                                {{--                                {!! Form::file('image', [ --}}
                                {{--                                    'id' => 'upload_image', --}}
                                {{--                                    'accept' => 'image/*', --}}
                                {{--                                    'required' => $is_image_required, --}}
                                {{--                                    'class' => 'upload-element', --}}
                                {{--                                ]) !!} --}}
                                {{--                                 <p><span> Click </span>to upload your product image</p> --}}
                                {{--                                 <small >@lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</small> --}}
                                {{--                            </div> --}}

                                <div class="product_image_form">
                                    <svg width="45" height="40" viewBox="0 0 45 40" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M22.5 21.172L30.986 29.656L28.156 32.486L24.5 28.83V40H20.5V28.826L16.844 32.486L14.014 29.656L22.5 21.172ZM22.5 4.4432e-08C25.934 0.00016354 29.2482 1.26223 31.8124 3.54624C34.3767 5.83025 36.0122 8.97693 36.408 12.388C38.8966 13.0666 41.0675 14.5982 42.5414 16.7151C44.0152 18.8319 44.6983 21.3994 44.4713 23.9688C44.2442 26.5382 43.1214 28.9461 41.2992 30.7716C39.4769 32.5972 37.071 33.7243 34.502 33.956V29.928C35.4224 29.7966 36.3073 29.4831 37.1052 29.0059C37.9031 28.5288 38.5979 27.8974 39.1492 27.1488C39.7004 26.4002 40.097 25.5493 40.3158 24.6457C40.5346 23.7421 40.5712 22.804 40.4235 21.8861C40.2758 20.9683 39.9467 20.089 39.4555 19.2997C38.9642 18.5104 38.3207 17.8268 37.5624 17.2889C36.8042 16.751 35.9464 16.3696 35.0391 16.1668C34.1317 15.9641 33.1931 15.9441 32.278 16.108C32.5913 14.6498 32.5743 13.1399 32.2285 11.6891C31.8826 10.2383 31.2166 8.88317 30.2792 7.72306C29.3418 6.56295 28.1568 5.62721 26.811 4.98439C25.4651 4.34157 23.9925 4.00794 22.501 4.00794C21.0095 4.00794 19.5369 4.34157 18.1911 4.98439C16.8452 5.62721 15.6602 6.56295 14.7228 7.72306C13.7855 8.88317 13.1194 10.2383 12.7736 11.6891C12.4277 13.1399 12.4108 14.6498 12.724 16.108C10.8993 15.7653 9.01327 16.1616 7.48072 17.2095C5.94817 18.2575 4.89469 19.8713 4.55203 21.696C4.20937 23.5207 4.6056 25.4068 5.65356 26.9393C6.70151 28.4719 8.31534 29.5253 10.14 29.868L10.5 29.928V33.956C7.93093 33.7247 5.52484 32.5978 3.7023 30.7724C1.87976 28.947 0.756686 26.5391 0.529381 23.9697C0.302075 21.4002 0.984991 18.8326 2.45877 16.7155C3.93255 14.5985 6.10345 13.0668 8.59203 12.388C8.98744 8.97675 10.6228 5.82982 13.1872 3.54573C15.7515 1.26164 19.0659 -0.000273405 22.5 4.4432e-08Z"
                                            fill="#767676" />
                                    </svg>
                                    {!! Form::file('image', [
                                        'id' => 'upload_image',
                                        'accept' => 'image/*',
                                        'required' => $is_image_required,
                                        'class' => 'upload-element',
                                    ]) !!}

                                    <p><span> Click </span>to upload your product image</p>
                                    <small>@lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</small>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="product_brochure">
                        <h2 class="product_item_header">Product brochure</h2>
                        <div class="">
                            <div class="product_image_wrapper">
                                {{--                           <div class="col-sm-4"> --}}
                                {{--                            <div class="form-group"> --}}
                                {{--                                {!! Form::label('product_brochure', __('lang_v1.product_brochure') . ':') !!} --}}
                                {{--                                {!! Form::file('product_brochure', [ --}}
                                {{--                                    'id' => 'product_brochure', --}}
                                {{--                                    'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types'))), --}}
                                {{--                                ]) !!} --}}
                                {{--                                <small> --}}
                                {{--                                    <p class="help-block"> --}}
                                {{--                                        @lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000]) --}}
                                {{--                                        @includeIf('components.document_help_text') --}}
                                {{--                                    </p> --}}
                                {{--                                </small> --}}
                                {{--                            </div> --}}
                                {{--                        </div> --}}
                                <div class="product_image_form product_brochure_form">
                                    <svg width="45" height="40" viewBox="0 0 45 40" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M22.5 21.172L30.986 29.656L28.156 32.486L24.5 28.83V40H20.5V28.826L16.844 32.486L14.014 29.656L22.5 21.172ZM22.5 4.4432e-08C25.934 0.00016354 29.2482 1.26223 31.8124 3.54624C34.3767 5.83025 36.0122 8.97693 36.408 12.388C38.8966 13.0666 41.0675 14.5982 42.5414 16.7151C44.0152 18.8319 44.6983 21.3994 44.4713 23.9688C44.2442 26.5382 43.1214 28.9461 41.2992 30.7716C39.4769 32.5972 37.071 33.7243 34.502 33.956V29.928C35.4224 29.7966 36.3073 29.4831 37.1052 29.0059C37.9031 28.5288 38.5979 27.8974 39.1492 27.1488C39.7004 26.4002 40.097 25.5493 40.3158 24.6457C40.5346 23.7421 40.5712 22.804 40.4235 21.8861C40.2758 20.9683 39.9467 20.089 39.4555 19.2997C38.9642 18.5104 38.3207 17.8268 37.5624 17.2889C36.8042 16.751 35.9464 16.3696 35.0391 16.1668C34.1317 15.9641 33.1931 15.9441 32.278 16.108C32.5913 14.6498 32.5743 13.1399 32.2285 11.6891C31.8826 10.2383 31.2166 8.88317 30.2792 7.72306C29.3418 6.56295 28.1568 5.62721 26.811 4.98439C25.4651 4.34157 23.9925 4.00794 22.501 4.00794C21.0095 4.00794 19.5369 4.34157 18.1911 4.98439C16.8452 5.62721 15.6602 6.56295 14.7228 7.72306C13.7855 8.88317 13.1194 10.2383 12.7736 11.6891C12.4277 13.1399 12.4108 14.6498 12.724 16.108C10.8993 15.7653 9.01327 16.1616 7.48072 17.2095C5.94817 18.2575 4.89469 19.8713 4.55203 21.696C4.20937 23.5207 4.6056 25.4068 5.65356 26.9393C6.70151 28.4719 8.31534 29.5253 10.14 29.868L10.5 29.928V33.956C7.93093 33.7247 5.52484 32.5978 3.7023 30.7724C1.87976 28.947 0.756686 26.5391 0.529381 23.9697C0.302075 21.4002 0.984991 18.8326 2.45877 16.7155C3.93255 14.5985 6.10345 13.0668 8.59203 12.388C8.98744 8.97675 10.6228 5.82982 13.1872 3.54573C15.7515 1.26164 19.0659 -0.000273405 22.5 4.4432e-08Z"
                                            fill="#767676" />
                                    </svg>
                                    {!! Form::file('product_brochure', [
                                        'id' => 'product_brochure',
                                        'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types'))),
                                    ]) !!}
                                    <p><span> Click </span>to upload your product image</p>
                                    <small>
                                        @lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000])
                                        @includeIf('components.document_help_text')
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if (session('business.enable_racks') || session('business.enable_row') || session('business.enable_position'))
                        <div>
                            <h2 class="product_item_header">@lang('lang_v1.rack_details'):
                                @show_tooltip(__('lang_v1.tooltip_rack_details'))</h2>

                            <div class="product_details_wrapper">
                                @foreach ($business_locations as $id => $location)
                                    <div class="product_detiles_items">
                                        <div class="form-group addProduct_form">
                                            {!! Form::label('rack_' . $id, $location . ':') !!}

                                            @if (session('business.enable_racks'))
                                                <div class=" form-group addProduct_form">
                                                    {!! Form::label('name', __('lang_v1.rack') . ':*') !!}
                                                    {!! Form::text(
                                                        'product_racks[' . $id . '][rack]',
                                                        !empty($rack_details[$id]['rack']) ? $rack_details[$id]['rack'] : null,
                                                        ['class' => 'form-control', 'id' => 'rack_' . $id, 'placeholder' => __('lang_v1.rack')],
                                                    ) !!}
                                                </div>
                                            @endif

                                            @if (session('business.enable_row'))
                                                <div class="form-group addProduct_form">
                                                    {!! Form::label('name', __('lang_v1.row') . ':*') !!}

                                                    {!! Form::text(
                                                        'product_racks[' . $id . '][row]',
                                                        !empty($rack_details[$id]['row']) ? $rack_details[$id]['row'] : null,
                                                        ['class' => 'form-control', 'placeholder' => __('lang_v1.row')],
                                                    ) !!}
                                                </div>
                                            @endif

                                            @if (session('business.enable_position'))
                                                <div class="form-group addProduct_form">
                                                    {!! Form::label('name', __('lang_v1.position') . ':*') !!}

                                                    {!! Form::text(
                                                        'product_racks[' . $id . '][position]',
                                                        !empty($rack_details[$id]['position']) ? $rack_details[$id]['position'] : null,
                                                        ['class' => 'form-control', 'placeholder' => __('lang_v1.position')],
                                                    ) !!}
                                                </div>
                                            @endif
                                            <div class="">
                                                <div class="form-group addProduct_form">
                                                    {!! Form::label('preparation_time_in_minutes', __('lang_v1.preparation_time_in_minutes') . ':') !!}
                                                    {!! Form::number(
                                                        'preparation_time_in_minutes',
                                                        !empty($duplicate_product->preparation_time_in_minutes) ? $duplicate_product->preparation_time_in_minutes : null,
                                                        ['class' => 'form-control', 'placeholder' => __('lang_v1.preparation_time_in_minutes')],
                                                    ) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="product_detiles_items">
                                    <div class="form-group addProduct_form addProduct_form_checkbox">
                                        <br>
                                        <label>
                                            {!! Form::checkbox('enable_sr_no', 1, !empty($duplicate_product) ? $duplicate_product->enable_sr_no : false, [
                                                'class' => 'input-icheck',
                                            ]) !!} <strong>@lang('lang_v1.enable_imei_or_sr_no')</strong>
                                        </label> @show_tooltip(__('lang_v1.tooltip_sr_no'))
                                    </div>
                                </div>

                                <div class="product_detiles_items">
                                    <div class="form-group addProduct_form addProduct_form_checkbox">
                                        <br>
                                        <label>
                                            {!! Form::checkbox(
                                                'not_for_selling',
                                                1,
                                                !empty($duplicate_product) ? $duplicate_product->not_for_selling : false,
                                                ['class' => 'input-icheck'],
                                            ) !!} <strong>@lang('lang_v1.not_for_selling')</strong>
                                        </label> @show_tooltip(__('lang_v1.tooltip_not_for_selling'))
                                    </div>
                                </div>
                                <button class="add_custom_fildes">

                                    <svg width="13" height="12" viewBox="0 0 13 12" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M5.66675 5.16699V0.166992H7.33342V5.16699H12.3334V6.83366H7.33342V11.8337H5.66675V6.83366H0.666748V5.16699H5.66675Z"
                                            fill="black" />
                                    </svg>
                                    Add Custom fields

                                </button>
                                <div>
                                    @php
                                        $custom_labels = json_decode(session('business.custom_labels'), true);
                                        $product_custom_field1 = !empty($custom_labels['product']['custom_field_1']) ? $custom_labels['product']['custom_field_1'] : __('lang_v1.product_custom_field1');
                                        $product_custom_field2 = !empty($custom_labels['product']['custom_field_2']) ? $custom_labels['product']['custom_field_2'] : __('lang_v1.product_custom_field2');
                                        $product_custom_field3 = !empty($custom_labels['product']['custom_field_3']) ? $custom_labels['product']['custom_field_3'] : __('lang_v1.product_custom_field3');
                                        $product_custom_field4 = !empty($custom_labels['product']['custom_field_4']) ? $custom_labels['product']['custom_field_4'] : __('lang_v1.product_custom_field4');
                                    @endphp
                                    <!--custom fields-->

                                    <div class="f_coustom_fields hide">
                                        <div class="product_detiles_items">
                                            <div class="form-group addProduct_form">
                                                {!! Form::label('product_custom_field1', $product_custom_field1 . ':') !!}
                                                {!! Form::text(
                                                    'product_custom_field1',
                                                    !empty($duplicate_product->product_custom_field1) ? $duplicate_product->product_custom_field1 : null,
                                                    ['class' => 'form-control', 'placeholder' => $product_custom_field1],
                                                ) !!}
                                            </div>
                                        </div>

                                        <div class="product_detiles_items">
                                            <div class="form-group addProduct_form">
                                                {!! Form::label('product_custom_field2', $product_custom_field2 . ':') !!}
                                                {!! Form::text(
                                                    'product_custom_field2',
                                                    !empty($duplicate_product->product_custom_field2) ? $duplicate_product->product_custom_field2 : null,
                                                    ['class' => 'form-control', 'placeholder' => $product_custom_field2],
                                                ) !!}
                                            </div>
                                        </div>

                                        <div class="product_detiles_items">
                                            <div class="form-group addProduct_form">
                                                {!! Form::label('product_custom_field3', $product_custom_field3 . ':') !!}
                                                {!! Form::text(
                                                    'product_custom_field3',
                                                    !empty($duplicate_product->product_custom_field3) ? $duplicate_product->product_custom_field3 : null,
                                                    ['class' => 'form-control', 'placeholder' => $product_custom_field3],
                                                ) !!}
                                            </div>
                                        </div>

                                        <div class="product_detiles_items">
                                            <div class="form-group addProduct_form">
                                                {!! Form::label('product_custom_field4', $product_custom_field4 . ':') !!}
                                                {!! Form::text(
                                                    'product_custom_field4',
                                                    !empty($duplicate_product->product_custom_field4) ? $duplicate_product->product_custom_field4 : null,
                                                    ['class' => 'form-control', 'placeholder' => $product_custom_field4],
                                                ) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endif
                </div>
            </div>
            <div class="product_tax_detiles">
                <h2 class="product_item_header">Product Tax Details </h2>
                <div class="product_tax_detiles_from">
                    <div class="row product_tax_input">
                        <div class="col-sm-4 @if (!session('business.enable_price_tax')) hide @endif">
                            <div class="form-group addProduct_form">
                                {!! Form::label('tax', __('product.applicable_tax') . ':') !!}
                                {!! Form::select(
                                    'tax',
                                    $taxes,
                                    !empty($duplicate_product->tax) ? $duplicate_product->tax : null,
                                    ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'],
                                    $tax_attributes,
                                ) !!}
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group addProduct_form">
                                {!! Form::label('type', __('product.product_type') . ':*') !!} @show_tooltip(__('tooltip.product_type'))
                                {!! Form::select('type', $product_types, !empty($duplicate_product->type) ? $duplicate_product->type : null, [
                                    'class' => 'form-control select2',
                                    'required',
                                    'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add',
                                    'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0',
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-sm-4 @if (!session('business.enable_price_tax')) hide @endif">
                            <div class="form-group addProduct_form">
                                {!! Form::label('tax_type', __('product.selling_price_tax_type') . ':*') !!}
                                {!! Form::select(
                                    'tax_type',
                                    ['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')],
                                    !empty($duplicate_product->tax_type) ? $duplicate_product->tax_type : 'exclusive',
                                    ['class' => 'form-control select2', 'required'],
                                ) !!}
                            </div>
                        </div>

                    </div>
                    <div class="form-group addProduct_form " id="product_form_part">
                        @include('product.partials.single_product_form_part', [
                            'profit_percent' => $default_profit_percent,
                        ])
                    </div>
                </div>
            </div>
            <div class="row">

                <div class="clearfix"></div>



                <!-- include module fields -->
                @if (!empty($pos_module_data))
                    @foreach ($pos_module_data as $key => $value)
                        @if (!empty($value['view_path']))
                            @includeIf($value['view_path'], ['view_data' => $value['view_data']])
                        @endif
                    @endforeach
                @endif
                <div class="clearfix"></div>


            </div>

        @endcomponent

        {{-- @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                @if (session('business.enable_product_expiry'))
                    @if (session('business.expiry_type') == 'add_expiry')
                        @php
                            $expiry_period = 12;
                            $hide = true;
                        @endphp
                    @else
                        @php
                            $expiry_period = null;
                            $hide = false;
                        @endphp
                    @endif
                    <div class="col-sm-4 @if ($hide) hide @endif">
                        <div class="form-group">
                            <div class="multi-input">
                                {!! Form::label('expiry_period', __('product.expires_in') . ':') !!}<br>
                                {!! Form::text(
                                    'expiry_period',
                                    !empty($duplicate_product->expiry_period) ? @num_format($duplicate_product->expiry_period) : $expiry_period,
                                    [
                                        'class' => 'form-control pull-left input_number',
                                        'placeholder' => __('product.expiry_period'),
                                        'style' => 'width:60%;',
                                    ],
                                ) !!}
                                {!! Form::select(
                                    'expiry_period_type',
                                    ['months' => __('product.months'), 'days' => __('product.days'), '' => __('product.not_applicable')],
                                    !empty($duplicate_product->expiry_period_type) ? $duplicate_product->expiry_period_type : 'months',
                                    ['class' => 'form-control select2 pull-left', 'style' => 'width:40%;', 'id' => 'expiry_period_type'],
                                ) !!}
                            </div>
                        </div>
                    </div>
                @endif



                <div class="clearfix"></div>

                <!-- Rack, Row & position number -->
                {{--                @if (session('business.enable_racks') || session('business.enable_row') || session('business.enable_position')) --}}
                {{--                    <div class="col-md-12"> --}}
                {{--                        <h4>@lang('lang_v1.rack_details'): --}}
                {{--                            @show_tooltip(__('lang_v1.tooltip_rack_details')) --}}
                {{--                        </h4> --}}
                {{--                    </div> --}}
                {{--                    @foreach ($business_locations as $id => $location) --}}
                {{--                        <div class="col-sm-3"> --}}
                {{--                            <div class="form-group"> --}}
                {{--                                {!! Form::label('rack_' . $id, $location . ':') !!} --}}

                {{--                                @if (session('business.enable_racks')) --}}
                {{--                                    {!! Form::text( --}}
                {{--                                        'product_racks[' . $id . '][rack]', --}}
                {{--                                        !empty($rack_details[$id]['rack']) ? $rack_details[$id]['rack'] : null, --}}
                {{--                                        ['class' => 'form-control', 'id' => 'rack_' . $id, 'placeholder' => __('lang_v1.rack')], --}}
                {{--                                    ) !!} --}}
                {{--                                @endif --}}

                {{--                                @if (session('business.enable_row')) --}}
                {{--                                    {!! Form::text( --}}
                {{--                                        'product_racks[' . $id . '][row]', --}}
                {{--                                        !empty($rack_details[$id]['row']) ? $rack_details[$id]['row'] : null, --}}
                {{--                                        ['class' => 'form-control', 'placeholder' => __('lang_v1.row')], --}}
                {{--                                    ) !!} --}}
                {{--                                @endif --}}

                {{--                                @if (session('business.enable_position')) --}}
                {{--                                    {!! Form::text( --}}
                {{--                                        'product_racks[' . $id . '][position]', --}}
                {{--                                        !empty($rack_details[$id]['position']) ? $rack_details[$id]['position'] : null, --}}
                {{--                                        ['class' => 'form-control', 'placeholder' => __('lang_v1.position')], --}}
                {{--                                    ) !!} --}}
                {{--                                @endif --}}
                {{--                            </div> --}}
                {{--                        </div> --}}
                {{--                    @endforeach --}}
                {{--                @endif 





                <!--custom fields-->
                <div class="clearfix"></div>
                @include('layouts.partials.module_form_part')
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">



                <div class="clearfix"></div>




                <input type="hidden" id="variation_counter" value="1">
                <input type="hidden" id="default_profit_percent" value="{{ $default_profit_percent }}">

            </div>
        @endcomponent --}}
        <div class="row">
            <div class="col-sm-12">
                <input type="hidden" name="submit_type" id="submit_type">
                <div class="text-center">
                    <div class="btn-group addproduct_btns">
                        @if ($selling_price_group_count)
                            <button type="submit" value="submit_n_add_selling_prices"
                                class="btn  submit_product_form selling_prices_btn">@lang('lang_v1.save_n_add_selling_price_group_prices')</button>
                        @endif

                        @can('product.opening_stock')
                            <button id="opening_stock_button" @if (!empty($duplicate_product) && $duplicate_product->enable_stock == 0) disabled @endif
                                type="submit" value="submit_n_add_opening_stock"
                                class="btn submit_product_form opening_stock_btn">@lang('lang_v1.save_n_add_opening_stock')</button>
                        @endcan

                        <button type="submit" value="save_n_add_another"
                            class="btn  submit_product_form add_another_btn">@lang('lang_v1.save_n_add_another')</button>

                        <button type="submit" value="submit"
                            class="btn submit_product_form product_save_btn">@lang('messages.save')</button>
                    </div>

                </div>
            </div>
        </div>
        {!! Form::close() !!}

    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    @php $asset_v = env('APP_VERSION'); @endphp
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            __page_leave_confirmation('#product_add_form');
            onScan.attachTo(document, {
                suffixKeyCodes: [13], // enter-key expected at the end of a scan
                reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
                onScan: function(sCode, iQty) {
                    $('input#sku').val(sCode);
                },
                onScanError: function(oDebug) {
                    console.log(oDebug);
                },
                minLength: 2,
                ignoreIfFocusOn: ['input', '.form-control']
                // onKeyDetect: function(iKeyCode){ // output all potentially relevant key events - great for debugging!
                //     console.log('Pressed: ' + iKeyCode);
                // }
            });
        });
    </script>
@endsection
