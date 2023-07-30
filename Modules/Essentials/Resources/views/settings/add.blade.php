@extends('layouts.app')
@section('title', __('essentials::lang.essentials_n_hrm_settings'))

@section('content')
    @include('essentials::layouts.nav_hrm')


    <!-- Main content -->
    <section class="content" style="padding: 15px 0">
        <!-- Content Header (Page header) -->
        <section class="content-header f_content-header f_product_content-header">
            <h1>@lang('essentials::lang.essentials_n_hrm_settings')</h1>
        </section>
        {!! Form::open([
            'action' => '\Modules\Essentials\Http\Controllers\EssentialsSettingsController@update',
            'method' => 'post',
            'id' => 'essentials_settings_form',
        ]) !!}
        <div class="row">
            <div class="col-xs-12">
                <!--  <pos-tab-container> -->
                <div class="col-xs-12 pos-tab-container f_pos-tab-container">
                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 pos-tab-menu  f_pos-tab-menu">
                        <div class="list-group" style="padding: 15px;">
                            <a href="#"
                                class="list-group-item text-center f_list-group-item active">@lang('essentials::lang.leave')</a>
                            <a href="#" class="list-group-item text-center f_list-group-item">@lang('essentials::lang.payroll')</a>
                            <a href="#" class="list-group-item text-center f_list-group-item">@lang('essentials::lang.attendance')</a>
                            <a href="#" class="list-group-item text-center f_list-group-item">@lang('essentials::lang.sales_target')</a>
                            <a href="#" class="list-group-item text-center f_list-group-item">@lang('essentials::lang.essentials')</a>

                        </div>
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                        @include('essentials::settings.partials.leave_settings')
                        @include('essentials::settings.partials.payroll_settings')
                        @include('essentials::settings.partials.attendance_settings')
                        @include('essentials::settings.partials.sales_target_settings')
                        @include('essentials::settings.partials.essentials_settings')
                    </div>
                    {{-- <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                        @include('essentials::settings.partials.leave_settings')
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                        @include('essentials::settings.partials.payroll_settings')
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                        @include('essentials::settings.partials.attendance_settings')
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                        @include('essentials::settings.partials.sales_target_settings')
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                        @include('essentials::settings.partials.essentials_settings')
                    </div> --}}
                </div>

                <!--  </pos-tab-container> -->
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group pull-right">
                    {{ Form::submit(__('messages.update'), ['class' => 'btn f_btn-primary']) }}
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </section>
@stop
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            tinymce.init({
                selector: 'textarea#leave_instructions',
            });

            $('#essentials_settings_form').validate({
                ignore: [],
            });
        });
    </script>
@endsection
