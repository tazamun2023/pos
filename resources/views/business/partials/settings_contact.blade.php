<!--Purchase related settings -->
<div class="pos-tab-content" style="border-radius: 10px;padding: 20px !important;">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group addProduct_form">
                {!! Form::label('default_credit_limit',__('lang_v1.default_credit_limit') . ':') !!}
                {!! Form::text('common_settings[default_credit_limit]', $common_settings['default_credit_limit'] ?? '', ['class' => 'form-control input_number',
                'placeholder' => __('lang_v1.default_credit_limit'), 'id' => 'default_credit_limit']); !!}
            </div>
        </div>
    </div>
</div>