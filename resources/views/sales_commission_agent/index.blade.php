@extends('layouts.app')
@section('title', __('lang_v1.sales_commission_agents'))

@section('content')

<!-- Content Header (Page header) -->
{{-- <section class="content-header">
    <h1>@lang( 'lang_v1.sales_commission_agents' )
    </h1>
</section> --}}

<!-- Main content -->
<section class="content" style="padding: 15px 0;">
    <section class="content-header f_content-header f_product_content-header" style="margin-bottom: 25px">
        <h1>@lang( 'lang_v1.sales_commission_agents' )
        </h1>
        <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
            <li class="active">Here</li>
        </ol> -->
        {{-- <div>
            @can('user.create')                            
            <a class="btn f_add-btn pull-right" href="{{action([\App\Http\Controllers\SalesCommissionAgentController::class, 'create'])}}">
                        <i class="fa fa-plus"></i> @lang('messages.add')</a>
            <br><br>
        @endcan
        </div> --}}
        @can('user.create')
     
            <div class="box-tools">
                <button type="button" class="btn f_add-btn btn-modal pull-right"
                    data-href="{{action([\App\Http\Controllers\SalesCommissionAgentController::class, 'create'])}}" data-container=".commission_agent_modal">
                    <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
            </div>
       
    @endcan 
    </section>
    @component('components.widget', ['class' => 'box-primary'])
        {{-- @can('user.create')
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-primary btn-modal pull-right"
                        data-href="{{action([\App\Http\Controllers\SalesCommissionAgentController::class, 'create'])}}" data-container=".commission_agent_modal"><i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                </div>
            @endslot
        @endcan --}}
        @can('user.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="sales_commission_agent_table">
                    <thead>
                        <tr class="f_tr-th">
                            <th>@lang( 'user.name' )</th>
                            <th>@lang( 'business.email' )</th>
                            <th>@lang( 'lang_v1.contact_no' )</th>
                            <th>@lang( 'business.address' )</th>
                            <th>@lang( 'lang_v1.cmmsn_percent' )</th>
                            <th>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade commission_agent_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
