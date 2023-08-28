@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.packages'))

@section('content')
    @include('superadmin::layouts.nav')


    <!-- Main content -->
    <section class="content" style="padding: 15px 0;">
        <!-- Content Header (Page header) -->
        <section class="content-header  f_content-header f_product_content-header">
            <h1>@lang('superadmin::lang.packages') <small>@lang('superadmin::lang.all_packages')</small></h1>
            <!-- <ol class="breadcrumb">
                                    <a href="#"><i class="fa fa-dashboard"></i> Level</a>
                                    <li class="active">Here<br/>
                                </ol> -->
            <div class="box-tools">
                <a href="{{ action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'create']) }}"
                    class="btn btn-block f_add-btn">
                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
            </div>
        </section>
        @include('superadmin::layouts.partials.currency')

        <div class="box box-solid f_box">
            {{--            <div class="box-header"> --}}
            {{--                <h3 class="box-title">@lang('superadmin::lang.all_packages')</h3> --}}
            {{--            </div> --}}

            <div class="box-body">
                @foreach ($packages as $package)
                    <div class="col-md-4">

                        <div class="box f_box hvr-grow-shadow"
                            style="box-shadow: -2px 2px 14px 1px rgba(0, 0, 0, 0.1) !important">
                            <div class=" with-border text-center"
                                style="text-align: left;display: flex;align-items: center;justify-content: space-between;padding: 10px 0;border-bottom: 1px solid #f4f4f4">
                                <h2 class="box-title title " style="margin: 0">{{ $package->name }}</h2>

                                <div class="" style="display: flex;align-items: center;gap: 10px;">
                                    @if ($package->is_private)
                                        <a href="#!" class="btn btn-box-tool">
                                            <i class="fas fa-lock fa-lg text-info" data-toggle="tooltip"
                                                title="@lang('superadmin::lang.private_superadmin_only')"></i>
                                        </a>
                                    @endif

                                    @if ($package->is_one_time)
                                        <a href="#!" class="btn btn-box-tool">
                                            <i class="fas fa-dot-circle fa-lg text-info" data-toggle="tooltip"
                                                title="@lang('superadmin::lang.one_time_only_subscription')"></i>
                                        </a>
                                    @endif

                                    @if ($package->is_active == 1)
                                        <span class="badge bg-green">
                                            @lang('superadmin::lang.active')
                                        </span>
                                    @else
                                        <span class="badge bg-red">
                                            @lang('superadmin::lang.inactive')
                                        </span>
                                    @endif

                                    <a href="{{ action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'edit'], [$package->id]) }}"
                                        class="btn btn-box-tool" title="edit">
                                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M9.47313 0.875L8.22312 2.125H2.125V10.875H10.875V4.77688L12.125 3.52688V11.5C12.125 11.6658 12.0592 11.8247 11.9419 11.9419C11.8247 12.0592 11.6658 12.125 11.5 12.125H1.5C1.33424 12.125 1.17527 12.0592 1.05806 11.9419C0.940848 11.8247 0.875 11.6658 0.875 11.5V1.5C0.875 1.33424 0.940848 1.17527 1.05806 1.05806C1.17527 0.940848 1.33424 0.875 1.5 0.875H9.47313ZM11.8031 0.3125L12.6875 1.1975L6.9425 6.9425L6.06 6.94438L6.05875 6.05875L11.8031 0.3125Z"
                                                fill="black" />
                                        </svg>
                                    </a>
                                    <a href="{{ action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'destroy'], [$package->id]) }}"
                                        class="btn btn-box-tool link_confirmation" title="delete">
                                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M9.625 2.75H12.75V4H11.5V12.125C11.5 12.2908 11.4342 12.4497 11.3169 12.5669C11.1997 12.6842 11.0408 12.75 10.875 12.75H2.125C1.95924 12.75 1.80027 12.6842 1.68306 12.5669C1.56585 12.4497 1.5 12.2908 1.5 12.125V4H0.25V2.75H3.375V0.875C3.375 0.70924 3.44085 0.550268 3.55806 0.433058C3.67527 0.315848 3.83424 0.25 4 0.25H9C9.16576 0.25 9.32473 0.315848 9.44194 0.433058C9.55915 0.550268 9.625 0.70924 9.625 0.875V2.75ZM10.25 4H2.75V11.5H10.25V4ZM4.625 5.875H5.875V9.625H4.625V5.875ZM7.125 5.875H8.375V9.625H7.125V5.875ZM4.625 1.5V2.75H8.375V1.5H4.625Z"
                                                fill="black" />
                                        </svg>
                                    </a>

                                </div>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body text-center"
                                style="text-align: left; display: flex;flex-direction: column;gap: 8px;">
                                <div style="display: flex;align-items: center;gap: 8px">
                                    <span class="package_list_tip"></span>

                                    <span>
                                        @if ($package->location_count == 0)
                                            @lang('superadmin::lang.unlimited')
                                        @else
                                            {{ $package->location_count }}
                                        @endif

                                        @lang('business.business_locations')
                                    </span>
                                </div>

                                <div style="display: flex;align-items: center;gap: 8px">
                                    <span class="package_list_tip"></span>

                                    <span>

                                        @if ($package->user_count == 0)
                                            @lang('superadmin::lang.unlimited')
                                        @else
                                            {{ $package->user_count }}
                                        @endif

                                        @lang('superadmin::lang.users')
                                    </span>
                                </div>



                                <div style="display: flex;align-items: center;gap: 8px">
                                    <span class="package_list_tip"></span>

                                    <span>

                                        @if ($package->product_count == 0)
                                            @lang('superadmin::lang.unlimited')
                                        @else
                                            {{ $package->product_count }}
                                        @endif

                                        @lang('superadmin::lang.products')
                                    </span>
                                </div>

                                <div style="display: flex;align-items: center;gap: 8px">
                                    <span class="package_list_tip"></span>

                                    <span>

                                        @if ($package->invoice_count == 0)
                                            @lang('superadmin::lang.unlimited')
                                        @else
                                            {{ $package->invoice_count }}
                                        @endif

                                        @lang('superadmin::lang.invoices')
                                    </span>

                                </div>


                                <div style="display: flex;align-items: center;gap: 8px">
                                    <span class="package_list_tip"></span>

                                    <span>

                                        @if ($package->trial_days != 0)
                                            {{ $package->trial_days }} @lang('superadmin::lang.trial_days')
                                        @endif
                                    </span>
                                </div>
                                @if (!empty($package->custom_permissions))
                                    @foreach ($package->custom_permissions as $permission => $value)
                                        @isset($permission_formatted[$permission])
                                            <div style="display: flex;align-items: center;gap: 8px">
                                                <span class="package_list_tip"></span>

                                                <span>
                                                    {{ $permission_formatted[$permission] }}
                                                </span>
                                            </div>
                                        @endisset
                                    @endforeach
                                @endif

                                <h3 class="" style="text-align: left;">
                                    @if ($package->price != 0)
                                        <span class="display_currency" data-currency_symbol="true">
                                            {{ $package->price }}
                                        </span>

                                        <small>
                                            / {{ $package->interval_count }} {{ __('lang_v1.' . $package->interval) }}
                                        </small>
                                    @else
                                        @lang('superadmin::lang.free_for_duration', ['duration' => $package->interval_count . ' ' . __('lang_v1.' . $package->interval)])
                                    @endif
                                </h3>

                            </div>
                            <!-- /.box-body -->

                            <div class="box-footer " style="text-align: left;padding: 15px 0 !important">
                                {{ $package->description }}
                            </div>
                        </div>
                        <!-- /.box -->
                    </div>
                    @if ($loop->iteration % 3 == 0)
                        <div class="clearfix"></div>
                    @endif
                @endforeach

                <div class="col-md-12">
                    {{ $packages->links() }}
                </div>
            </div>

        </div>

        <div class="modal fade brands_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
