@extends('layouts.app')
@section('title', __('project::lang.project_report'))

@section('content')
    @include('project::layouts.nav')

    <section class="content" style="padding: 15px 0;">
        <!-- Content Header (Page header) -->
        <section class="content-header f_content-header f_product_content-header" style="margin-bottom: 25px">
            <h1>
                @lang('project::lang.project_report')
            </h1>
        </section>
        <div class="row">
            <div class="col-md-3">
                <div class="box f_box box-solid">
                    <div class="box-body ">
                        {{-- <i class="fas fa-hourglass-half fs-20"></i> <br> --}}
                        <span class="fs-20 project_log_report">
                            @lang('project::lang.time_log_report') <br>

                        </span>
                        <small class="project_by_employees" >
                            @lang('project::lang.by_employees')
                        </small>
                    </div>
                    <div class="box-footer " style="padding: 0 !important ;padding-top: 50px !important;">
                        <a href="{{ action([\Modules\Project\Http\Controllers\ReportController::class, 'getEmployeeTimeLogReport']) }}"
                            class="f_view">

                            @lang('messages.view')
                            <svg width="10" height="10" viewBox="0 0 5 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M2.12859 3.99866L0.00683594 1.87766L1.06734 0.816406L4.24959 3.99866L1.06734 7.18091L0.00683594 6.11966L2.12859 3.99866Z"
                                    fill="white" />
                            </svg>

                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="box f_box box-solid">
                    <div class="box-body ">
                        {{-- <i class="fas fa-hourglass-half fs-20"></i> <br> --}}
                        <span class="fs-20 project_log_report">
                            @lang('project::lang.time_log_report') <br>

                        </span>
                        <small class="project_by_employees">
                            @lang('project::lang.by_employees')
                        </small>
                    </div>
                    <div class="box-footer " style="padding: 0 !important ;padding-top: 50px !important;">
                        <a href="{{ action([\Modules\Project\Http\Controllers\ReportController::class, 'getProjectTimeLogReport']) }}"
                            class="f_view">

                            @lang('messages.view')
                            <svg width="10" height="10" viewBox="0 0 5 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M2.12859 3.99866L0.00683594 1.87766L1.06734 0.816406L4.24959 3.99866L1.06734 7.18091L0.00683594 6.11966L2.12859 3.99866Z"
                                    fill="white" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <link rel="stylesheet" href="{{ asset('modules/project/sass/project.css?v=' . $asset_v) }}">
    </section>
@endsection
