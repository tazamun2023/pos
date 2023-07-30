@extends('layouts.app')
@section('title', __('lang_v1.notification_templates'))

@section('content')



<!-- Main content -->
<section class="content" style="padding: 15px 0;">
    <!-- Content Header (Page header) -->
    <section class="content-header f_content-header f_product_content-header" style="margin-bottom: 25px">
    <h1>{{ __('lang_v1.notification_templates')}}</h1>
</section>
    {!! Form::open(['url' => action([\App\Http\Controllers\NotificationTemplateController::class, 'store']), 'method' => 'post' ]) !!}

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.notifications') . ':'])
                @include('notification_template.partials.tabs', ['templates' => $general_notifications])
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.customer_notifications') . ':'])
                @include('notification_template.partials.tabs', ['templates' => $customer_notifications])
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.supplier_notifications') . ':'])
                @include('notification_template.partials.tabs', ['templates' => $supplier_notifications])

                <div class="callout callout-warning">
                    <p>@lang('lang_v1.logo_not_work_in_sms'):</p>
                </div>
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 text-center">
            <button type="submit" class="btn f_btn-primary btn-big">@lang('messages.save')</button>
        </div>
    </div>
    {!! Form::close() !!}

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    $('textarea.ckeditor').each( function(){
        var editor_id = $(this).attr('id');
        tinymce.init({
            selector: 'textarea#'+editor_id,
        });
    });
</script>
@endsection