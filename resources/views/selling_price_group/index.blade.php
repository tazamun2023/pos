@extends('layouts.app')
@section('title', __('lang_v1.selling_price_group'))

@section('content')

<!-- Content Header (Page header) -->
{{-- <section class="content-header">
    <h1>@lang( 'lang_v1.selling_price_group' )
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section> --}}

<!-- Main content -->
<section class="content"  style="padding: 15px 0;">
    <section class="content-header f_content-header f_product_content-header" style="margin-bottom: 25px">
        <h1>@lang( 'lang_v1.selling_price_group' )
        </h1> 
       
        <div class="box-tools">
            <button type="button" class="btn f_add-btn btn-modal" 
                data-href="{{action([\App\Http\Controllers\SellingPriceGroupController::class, 'create'])}}" 
                data-container=".view_modal">
                <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
        </div>

    </section>
    @if (session('notification') || !empty($notification))
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    @if(!empty($notification['msg']))
                        {{$notification['msg']}}
                    @elseif(session('notification.msg'))
                        {{ session('notification.msg') }}
                    @endif
                </div>
            </div>  
        </div>     
    @endif 
<div class="row">
    <div class="col-sm-9">
        @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-sm-8 selling_price_header">
                <h4 class="">@lang('lang_v1.import_export_selling_price_group_prices'):</h4>
                <p>
                    &bull; @lang('lang_v1.price_group_import_istruction')
                </p>
                <p>
                    &bull; @lang('lang_v1.price_group_import_istruction1')
                </p>
                <p>
                    &bull; @lang('lang_v1.price_group_import_istruction2')
                </p>
            </div>
            <div class="col-sm-4" style="display: flex ; justify-content: flex-end">
                <a href="{{action([\App\Http\Controllers\SellingPriceGroupController::class, 'export'])}}" class="btn f_add-btn">@lang('lang_v1.export_selling_price_group_prices')</a>
            </div>
            
        
        </div>
@endcomponent
@component('components.widget', ['class' => 'box-primary', 'title' => __( 'lang_v1.all_selling_price_group' )])
   
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="selling_price_group_table">
            <thead>
                <tr class='f_tr-th'>
                    <th>@lang( 'lang_v1.name' )</th>
                    <th>@lang( 'lang_v1.description' )</th>
                    <th>@lang( 'messages.action' )</th>
                </tr>
            </thead>
        </table>
    </div>
@endcomponent
    </div>
    <div class="col-sm-3">
       
        <div class="col-sm-12">
            @component('components.widget', ['class' => 'box-primary'])
            {!! Form::open(['url' => action([\App\Http\Controllers\SellingPriceGroupController::class, 'import']), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
            <div class="form-group">
                {!! Form::label('name', __( 'product.file_to_import' ) . ':') !!}
                {!! Form::file('product_group_prices', ['required' => 'required']); !!}
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn f_add-btn">@lang('messages.submit')</button>
            </div>
            {!! Form::close() !!}
   
        @endcomponent
        </div>
    </div>
</div>
    
    <div class="modal fade brands_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    $(document).ready( function(){
        
        //selling_price_group_table
        var selling_price_group_table = $('#selling_price_group_table').DataTable({
                        processing: true,
                        serverSide: true,
                        buttons: [],
                        ajax: '/selling-price-group',
                        columnDefs: [ {
                            "targets": 2,
                            "orderable": false,
                            "searchable": false
                        } ]
                    });

        $(document).on('submit', 'form#selling_price_group_form', function(e){
            e.preventDefault();
            var data = $(this).serialize();

            $.ajax({
                method: "POST",
                url: $(this).attr("action"),
                dataType: "json",
                data: data,
                success: function(result){
                    if(result.success == true){
                        $('div.view_modal').modal('hide');
                        toastr.success(result.msg);
                        selling_price_group_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });

        $(document).on('click', 'button.delete_spg_button', function(){
            swal({
              title: LANG.sure,
              icon: "warning",
              buttons: true,
              dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: "DELETE",
                        url: href,
                        dataType: "json",
                        data: data,
                        success: function(result){
                            if(result.success == true){
                                toastr.success(result.msg);
                                selling_price_group_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });

        $(document).on('click', 'button.activate_deactivate_spg', function(){
            var href = $(this).data('href');
                $.ajax({
                    url: href,
                    dataType: "json",
                    success: function(result){
                        if(result.success == true){
                            toastr.success(result.msg);
                            selling_price_group_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
        });

    });
</script>
@endsection
