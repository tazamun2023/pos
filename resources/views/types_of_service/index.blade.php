@extends('layouts.app')
@section('title', __('lang_v1.types_of_service'))

@section('content')



    <!-- Main content -->
    <section class="content" style="padding: 15px 0">
        <!-- Content Header (Page header) -->
        <section class="content-header f_content-header f_product_content-header">
            <h1>@lang('lang_v1.types_of_service') @show_tooltip(__('lang_v1.types_of_service_help_long'))
            </h1>
            <div>
                <button type="button" class="btn btn-block f_add-btn btn-modal"
                    data-href="{{ action([\App\Http\Controllers\TypesOfServiceController::class, 'create']) }}"
                    data-container=".type_of_service_modal">
                    <i class="fa fa-plus"></i> @lang('messages.add')</button>
            </div>
        </section>
        @component('components.widget', ['class' => 'box-primary'])
            @can('brand.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="types_of_service_table">
                        <thead>
                            <tr class="f_tr-th">
                                <th>@lang('tax_rate.name')</th>
                                <th>@lang('lang_v1.description')</th>
                                <th>@lang('lang_v1.packing_charge')</th>
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcan
        @endcomponent

        <div class="modal fade type_of_service_modal contains_select2" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
