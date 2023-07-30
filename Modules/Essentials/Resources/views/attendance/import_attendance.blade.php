<div class="row">
    <div class="col-sm-12">
        {!! Form::open([
            'url' => action([\Modules\Essentials\Http\Controllers\AttendanceController::class, 'importAttendance']),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ]) !!}
        {{-- <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-8">
                    <div class="form-group ">
                    </div>
                </div>

            </div>
        </div> --}}
        <div class="product_image">
            <h2 class="product_item_header"> {!! Form::label('name', __('product.file_to_import') . ':') !!}
            </h2>
            <div class="">
                <div class="product_image_wrapper">


                    <div class="product_image_form" style="overflow: hidden">
                        <svg width="45" height="40" viewBox="0 0 45 40" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M22.5 21.172L30.986 29.656L28.156 32.486L24.5 28.83V40H20.5V28.826L16.844 32.486L14.014 29.656L22.5 21.172ZM22.5 4.4432e-08C25.934 0.00016354 29.2482 1.26223 31.8124 3.54624C34.3767 5.83025 36.0122 8.97693 36.408 12.388C38.8966 13.0666 41.0675 14.5982 42.5414 16.7151C44.0152 18.8319 44.6983 21.3994 44.4713 23.9688C44.2442 26.5382 43.1214 28.9461 41.2992 30.7716C39.4769 32.5972 37.071 33.7243 34.502 33.956V29.928C35.4224 29.7966 36.3073 29.4831 37.1052 29.0059C37.9031 28.5288 38.5979 27.8974 39.1492 27.1488C39.7004 26.4002 40.097 25.5493 40.3158 24.6457C40.5346 23.7421 40.5712 22.804 40.4235 21.8861C40.2758 20.9683 39.9467 20.089 39.4555 19.2997C38.9642 18.5104 38.3207 17.8268 37.5624 17.2889C36.8042 16.751 35.9464 16.3696 35.0391 16.1668C34.1317 15.9641 33.1931 15.9441 32.278 16.108C32.5913 14.6498 32.5743 13.1399 32.2285 11.6891C31.8826 10.2383 31.2166 8.88317 30.2792 7.72306C29.3418 6.56295 28.1568 5.62721 26.811 4.98439C25.4651 4.34157 23.9925 4.00794 22.501 4.00794C21.0095 4.00794 19.5369 4.34157 18.1911 4.98439C16.8452 5.62721 15.6602 6.56295 14.7228 7.72306C13.7855 8.88317 13.1194 10.2383 12.7736 11.6891C12.4277 13.1399 12.4108 14.6498 12.724 16.108C10.8993 15.7653 9.01327 16.1616 7.48072 17.2095C5.94817 18.2575 4.89469 19.8713 4.55203 21.696C4.20937 23.5207 4.6056 25.4068 5.65356 26.9393C6.70151 28.4719 8.31534 29.5253 10.14 29.868L10.5 29.928V33.956C7.93093 33.7247 5.52484 32.5978 3.7023 30.7724C1.87976 28.947 0.756686 26.5391 0.529381 23.9697C0.302075 21.4002 0.984991 18.8326 2.45877 16.7155C3.93255 14.5985 6.10345 13.0668 8.59203 12.388C8.98744 8.97675 10.6228 5.82982 13.1872 3.54573C15.7515 1.26164 19.0659 -0.000273405 22.5 4.4432e-08Z"
                                fill="#767676" />
                        </svg>
                        {{-- {!! Form::file('image', [
                                'id' => 'upload_image',
                                'accept' => 'image/*',
                                'required' => $is_image_required,
                                'class' => 'upload-element',
                            ]) !!} --}}
                        {!! Form::file('attendance', ['accept' => '.xls', 'required' => 'required', 'class' => 'upload-attendance']) !!}


                        <p><span> Click </span>to upload your product image</p>
                        <small>@lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</small>
                    </div>
                    <div class="" style="margin: 20px auto;width: 95%;margin-bottom: 0">

                        <button type="submit" class="btn f_btn-primary w-full">@lang('messages.submit')</button>
                    </div>
                </div>

            </div>
        </div>
        {!! Form::close() !!}
        <br><br>
        <div class="row">
            <div class="col-sm-4" style="margin-bottom: 20px">
                <a href="{{ asset('modules/essentials/files/import_attendance_template.xls') }}" class="btn btn-success"
                    download><i class="fa fa-download"></i> @lang('lang_v1.download_template_file')</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <table class="table" width="100%">
                    <tr class="f_tr-th">
                        <th>@lang('lang_v1.col_no')</th>
                        <th>@lang('lang_v1.col_name')</th>
                        <th>@lang('lang_v1.instruction')</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>@lang('business.email') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>{!! __('essentials::lang.email_ins') !!}</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>@lang('essentials::lang.clock_in_time') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>{!! __('essentials::lang.clock_in_time_ins') !!} ({{ \Carbon::now()->toDateTimeString() }})</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>@lang('essentials::lang.clock_out_time') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>{!! __('essentials::lang.clock_out_time_ins') !!} ({{ \Carbon::now()->toDateTimeString() }})</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>@lang('essentials::lang.clock_in_note') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>@lang('essentials::lang.clock_out_note') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>@lang('essentials::lang.ip_address') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
