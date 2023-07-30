@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.communicator'))

@section('content')
@include('superadmin::layouts.nav')


<!-- Main content -->
<section class="content" style="padding: 15px 0;">
	<!-- Content Header (Page header) -->
	<section class="content-header  f_content-header f_product_content-header">
    <h1>@lang('superadmin::lang.communicator')</h1>
</section>
	<div class="row">
		<div class="col-sm-12">
			<div class="box f_box box-solid">
				<div class="box-header" style="margin-bottom: 15px">
					<i class="fa fa-edit"></i>
					<h3 class="box-title">@lang('superadmin::lang.compose_message')</h3>
				</div>
		        <div class="box-body">
		        	{!! Form::open(['url' => action([\Modules\Superadmin\Http\Controllers\CommunicatorController::class, 'send']), 'method' => 'post', 'id' => 'communication_form']) !!}
		        		<div class="col-md-12 form-group addProduct_form ">
		        			{!! Form::label('recipients', __('superadmin::lang.recipients').':*') !!} <button type="button" class="btn f_btn-primary btn-xs select-all">@lang('lang_v1.select_all')</button> <button type="button" class="btn f_btn-primary btn-xs deselect-all">@lang('lang_v1.deselect_all')</button>
							{!! Form::select('recipients[]', $businesses, null, ['class' => 'form-control select2', 'required', 'multiple', 'id' => 'recipients']); !!}
		        		</div>
		        		<div class="col-md-12 form-group addProduct_form">
		        			{!! Form::label('subject', __('superadmin::lang.subject').':*') !!}
		        			{!! Form::text('subject', null, ['class' => 'form-control', 'required']); !!}
		        		</div>
		        		<div class="col-md-12 form-group addProduct_form">
		        			{!! Form::label('message', __('superadmin::lang.message').':*') !!}
		        			{!! Form::textarea('message', null, ['class' => 'form-control', 'required', 'rows' => 6]); !!}
		        		</div>
		        		<div class="col-md-12 form-group addProduct_form">
		        			<button type="submit" class="btn f_btn-primary pull-right" id="send_message">@lang('superadmin::lang.send')</button>
		        		</div>
		        	{!! Form::close() !!}
		        </div>
		    </div>
		</div>
		<div class="col-sm-12">
			<div class="box f_box">
				<div class="box-header" style="margin-bottom: 15px">
					<i class="fa fa-history"></i>
					<h3 class="box-title">@lang('superadmin::lang.message_history')</h3>
				</div>
		        <div class="box-body">
		        	<table class="table" id="message-history">
		        		<thead>
		        			<tr class="f_tr-th"> 
		        				<th>@lang('superadmin::lang.subject')</th>
		        				<th>@lang('superadmin::lang.message')</th>
		        				<th>@lang('lang_v1.date')</th>
		        			</tr>
		        		</thead>
		        	</table>
		        </div>
		     </div>
		</div>

	</div>
</section>
<!-- /.content -->
@stop
@section('javascript')

<script type="text/javascript">
	$(document).ready( function() {
		$('#send_message').click(function(e){
			e.preventDefault();
			if($('form#communication_form').valid()){
				swal({
	              title: LANG.sure,
	              icon: "warning",
	              buttons: true,
	              dangerMode: false,
				  buttons:[],
	            }).then((sure) => {
	            	if(sure){
	            		$('form#communication_form').submit();
	            	} else {
	            		return false;
	            	}
	            });
	        }
		});

		$('#message-history').DataTable({
			dom:'lfrtip',
			processing: true,
			serverSide: true,
			ajax: '{{action([\Modules\Superadmin\Http\Controllers\CommunicatorController::class, 'getHistory'])}}'
	    });

	    init_tinymce('message');
	});
</script>
@endsection