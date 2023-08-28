@extends('layouts.app')
@section('title', __('product.variations'))

@section('content')

    <!-- Content Header (Page header) -->
    {{-- <section class="content-header">
    <h1>@lang('product.variations')
        <small>@lang('lang_v1.manage_product_variations')</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section> --}}

    <!-- Main content -->
    <section class="content" style="padding: 15px 0;">
        <section class="content-header f_content-header f_product_content-header" style="margin-bottom: 25px">
            <h1>@lang('product.variations')
                <small>@lang('lang_v1.manage_product_variations')</small>
            </h1>
            <!-- <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                <li class="active">Here</li>
            </ol> -->
            <div>
                {{-- @can('user.create')                            
            <a class="btn f_add-btn pull-right" href="{{action([\App\Http\Controllers\VariationTemplateController::class, 'create'])}}">
                        <i class="fa fa-plus"></i> @lang('messages.add')</a>
            <br><br>
        @endcan --}}
                <div class="box-tools">
                    <button type="button" class="btn f_add-btn btn-modal"
                        data-href="{{ action([\App\Http\Controllers\VariationTemplateController::class, 'create']) }}"
                        data-container=".view_modal">
                        <i class="fa fa-plus"></i> @lang('messages.add')</button>
                </div>
            </div>
        </section>
        @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.all_variations')])
            {{-- @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                data-href="{{action([\App\Http\Controllers\VariationTemplateController::class, 'create'])}}" 
                data-container=".variation_modal">
                <i class="fa fa-plus"></i> @lang('messages.add')</button>
            </div>
        @endslot --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="variation_table">
                    <thead>
                        <tr class='f_tr-th'>
                            <th>@lang('product.variations')</th>
                            <th>@lang('lang_v1.values')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade variation_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
