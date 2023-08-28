@extends('layouts.app')
@section('title', __( 'unit.units' ))

@section('content')

<!-- Content Header (Page header) -->
{{-- <section class="content-header">
    <h1>@lang( 'unit.units' )
        <small>@lang( 'unit.manage_your_units' )</small>
    </h1>
  
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section> --}}


<!-- Main content -->
<section class="content" style="padding: 15px 0;">

     <!-- Content Header (Page header) -->
 <section class="content-header f_content-header f_product_content-header" style="margin-bottom: 25px">
    <h1>@lang( 'unit.units' )
        <small>@lang( 'unit.manage_your_units' )</small>
    </h1>
    <!-- <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
    <li class="active">Here</li>
</ol> -->
    <div>
        @can('product.create')
            {{-- <a class="btn f_add-btn pull-right"
                href="{{action([\App\Http\Controllers\UnitController::class, 'create'])}}">
                <i class="fa fa-plus"></i> @lang('messages.add')</a>
            <br><br> --}}
            <div class="box-tools">
                <button type="button" class="btn btn-block f_add-btn btn-modal" 
                    data-href="{{action([\App\Http\Controllers\UnitController::class, 'create'])}}" 
                    data-container=".unit_modal">
                    <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
            </div>
        @endcan
    </div>
</section>
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'unit.all_your_units' )])
        {{-- @can('unit.create')
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal" 
                        data-href="{{action([\App\Http\Controllers\UnitController::class, 'create'])}}" 
                        data-container=".unit_modal">
                        <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                </div>
            @endslot
        @endcan --}}
        @can('unit.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="unit_table">
                    <thead>
                        <tr class="f_tr-th">
                            <th>@lang( 'unit.name' )</th>
                            <th>@lang( 'unit.short_name' )</th>
                            <th>@lang( 'unit.allow_decimal' ) @show_tooltip(__('tooltip.unit_allow_decimal'))</th>
                            <th width='20%'>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade unit_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
