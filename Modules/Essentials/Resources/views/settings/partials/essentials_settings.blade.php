<div class="pos-tab-content" style="border-radius: 10px;padding: 26px;" >
    <div class="row">
    	<div class="col-xs-4 f_leave_ref_no_prefix">
            <div class="form-group addProduct_form">
                {!! Form::label('essentials_todos_prefix',  __('essentials::lang.essentials_todos_prefix') . ':') !!}
                {!! Form::text('essentials_todos_prefix', !empty($settings['essentials_todos_prefix']) ? $settings['essentials_todos_prefix'] : null, ['class' => 'form-control','placeholder' => __('essentials::lang.essentials_todos_prefix')]); !!}
            </div>
        </div>
    </div>
</div>