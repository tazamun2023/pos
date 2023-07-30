@extends('layouts.app')
@section('title', __('repair::lang.repair') . ' ' . __('business.dashboard'))

@section('content')
    @include('repair::layouts.nav')

    <!-- Main content -->
    <section class="content no-print" style="padding: 15px 0">
        <!-- Content Header (Page header) -->
        <section class="content-header f_content-header f_product_content-header">
            <h1  class="title">
                @lang('repair::lang.repair')
                <small>@lang('business.dashboard') </small>
            </h1>
        </section>
        <div class="row">
            <div class="col-md-12">
                <div class="box f_box box-solid">
                    <div class="box-header with-border" style="margin-bottom: 15px">
                        <h4 class="box-title title">@lang('repair::lang.job_sheets_by_status')</h4>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            @forelse($job_sheets_by_status as $job_sheet)
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <div class="small-box" style="background-color: {{ $job_sheet->color }};color: #fff;">
                                        <div class="inner">
                                            <p>{{ $job_sheet->status_name }}</p>
                                            <h3>{{ $job_sheet->total_job_sheets }}</h3>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-md-12">
                                    <div class="alert">
                                        <h4 style="font-weight: 500;font-size: 20px;color: #A0A0A0;text-align: center">
                                            @lang('repair::lang.no_report_found')
                                        </h4>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if (in_array('service_staff', $enabled_modules))
            <div class="row">
                <div class="col-xs-12">
                    @component('components.widget')
                        @slot('title')
                            @lang('repair::lang.job_sheets_by_service_staff')
                        @endslot
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>@lang('restaurant.service_staff')</th>
                                        <th>@lang('repair::lang.total_job_sheets')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($job_sheets_by_service_staff as $job_sheet)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $job_sheet->service_staff }}</td>
                                            <td>{{ $job_sheet->total_job_sheets }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endcomponent
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-xs-12">
                @component('components.widget')
                    @slot('title')
                        @lang('repair::lang.trending_brands')
                    @endslot
                    {!! $trending_brand_chart->container() !!}
                @endcomponent
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                @component('components.widget')
                    @slot('title')
                        @lang('repair::lang.trending_devices')
                    @endslot
                    {!! $trending_devices_chart->container() !!}
                @endcomponent
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                @component('components.widget')
                    @slot('title')
                        @lang('repair::lang.trending_device_models')
                    @endslot
                    {!! $trending_dm_chart->container() !!}
                @endcomponent
            </div>
        </div>
    </section>
@stop
@section('javascript')
    {!! $trending_devices_chart->script() !!}
    {!! $trending_dm_chart->script() !!}
    {!! $trending_brand_chart->script() !!}
@endsection
