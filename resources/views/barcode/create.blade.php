@extends('layouts.app')
@section('title',  __('barcode.add_barcode_setting'))

@section('content')
<style type="text/css">



</style>


<!-- Main content -->
<section class="content" style="padding: 15px 0">
  <!-- Content Header (Page header) -->
  <section class="content-header f_content-header f_product_content-header">
  <h1>@lang('barcode.add_barcode_setting')</h1>
  <!-- <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
      <li class="active">Here</li>
  </ol> -->
</section>
{!! Form::open(['url' => action([\App\Http\Controllers\BarcodeController::class, 'store']), 'method' => 'post', 
'id' => 'add_barcode_settings_form' ]) !!}
	<div class="box box-solid">
    <div class="box-body">
      <div class="row">
        <div class="col-sm-12">
          <div class="form-group addProduct_form">
            {!! Form::label('name', __('barcode.setting_name') . ':*') !!}
              {!! Form::text('name', null, ['class' => 'form-control', 'required',
              'placeholder' => __('barcode.setting_name')]); !!}
          </div>
        </div>
        <div class="col-sm-12">
          <div class="form-group addProduct_form">
            {!! Form::label('description', __('barcode.setting_description') ) !!}
              {!! Form::textarea('description', null, ['class' => 'form-control',
              'placeholder' => __('barcode.setting_description'), 'rows' => 3]); !!}
          </div>
        </div>
        <div class="col-sm-12">
          <div class="form-group addProduct_form">
            <div class="checkbox">
              <label>
                {!! Form::checkbox('is_continuous', 1, false, ['id' => 'is_continuous']); !!} @lang('barcode.is_continuous')</label>
              </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group addProduct_form">
             {!! Form::label('top_margin', __('barcode.top_margin') . ' ('. __('barcode.in_in') . '):*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></span>
              </span>
              {!! Form::number('top_margin', 0, ['class' => 'form-control',
              'placeholder' => __('barcode.top_margin'), 'min' => 0, 'step' => 0.00001, 'required']); !!}
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group addProduct_form">
            {!! Form::label('left_margin', __('barcode.left_margin') . ' ('. __('barcode.in_in') . '):*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span>
              </span>
              {!! Form::number('left_margin', 0, ['class' => 'form-control',
              'placeholder' => __('barcode.left_margin'), 'min' => 0, 'step' => 0.00001, 'required']); !!}
            </div>
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6">
          <div class="form-group addProduct_form">
            {!! Form::label('width', __('barcode.width') . ' ('. __('barcode.in_in') . '):*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-text-width" aria-hidden="true"></i>
              </span>
              {!! Form::number('width', null, ['class' => 'form-control',
              'placeholder' => __('barcode.width'), 'min' => 0.1, 'step' => 0.00001, 'required']); !!}
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group addProduct_form">
            {!! Form::label('height', __('barcode.height') . ' ('. __('barcode.in_in') . '):*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-text-height" aria-hidden="true"></i>
              </span>
              {!! Form::number('height', null, ['class' => 'form-control',
              'placeholder' => __('barcode.height'), 'min' => 0.1, 'step' => 0.00001, 'required']); !!}
            </div>
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6">
          <div class="form-group addProduct_form">
            {!! Form::label('paper_width', __('barcode.paper_width') . ' ('. __('barcode.in_in') . '):*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-text-width" aria-hidden="true"></i>
              </span>
              {!! Form::number('paper_width', null, ['class' => 'form-control',
              'placeholder' => __('barcode.paper_width'), 'min' => 0.1, 'step' => 0.00001, 'required']); !!}
            </div>
          </div>
        </div>
        <div class="col-sm-6 paper_height_div">
          <div class="form-group addProduct_form">
            {!! Form::label('paper_height', __('barcode.paper_height') . ' ('. __('barcode.in_in') . '):*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-text-height" aria-hidden="true"></i>
              </span>
              {!! Form::number('paper_height', null, ['class' => 'form-control',
              'placeholder' => __('barcode.paper_height'), 'min' => 0.1, 'step' => 0.00001, 'required']); !!}
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group addProduct_form">
            {!! Form::label('stickers_in_one_row', __('barcode.stickers_in_one_row') . ':*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
              </span>
              {!! Form::number('stickers_in_one_row', null, ['class' => 'form-control',
              'placeholder' => __('barcode.stickers_in_one_row'), 'min' => 1, 'required']); !!}
            </div>
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6">
          <div class="form-group addProduct_form">
            {!! Form::label('row_distance', __('barcode.row_distance') . ' ('. __('barcode.in_in') . '):*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <span class="glyphicon glyphicon-resize-vertical" aria-hidden="true"></span>
              </span>
              {!! Form::number('row_distance', 0, ['class' => 'form-control',
              'placeholder' => __('barcode.row_distance'), 'min' => 0, 'step' => 0.00001, 'required']); !!}
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group addProduct_form">
            {!! Form::label('col_distance', __('barcode.col_distance') . ' ('. __('barcode.in_in') . '):*') !!}
             <div class="input-group">
              <span class="input-group-addon">
                <span class="glyphicon glyphicon-resize-horizontal" aria-hidden="true"></span>
              </span>
              {!! Form::number('col_distance', 0, ['class' => 'form-control',
              'placeholder' => __('barcode.col_distance'), 'min' => 0, 'step' => 0.00001, 'required']); !!}
            </div>
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6 stickers_per_sheet_div">
          <div class="form-group addProduct_form">
            {!! Form::label('stickers_in_one_sheet', __('barcode.stickers_in_one_sheet') . ':*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-th" aria-hidden="true"></i>
              </span>
              {!! Form::number('stickers_in_one_sheet', null, ['class' => 'form-control',
              'placeholder' => __('barcode.stickers_in_one_sheet'), 'min' => 1, 'required']); !!}
            </div>
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6">
          <div class="form-group addProduct_form">
            <div class="checkbox">
              <label>
                {!! Form::checkbox('is_default', 1); !!} @lang('barcode.set_as_default')</label>
              </div>
          </div>
        </div>
        <div class="col-sm-12">
          <button type="submit" class="btn f_btn-primary pull-right">@lang('messages.save')</button>
        </div>
      </div>
    </div>
  </div>
  {!! Form::close() !!}
</section>
<!-- /.content -->
@endsection