<div class="pos-tab-content" style="border-radius: 10px;padding: 26px;">
    <div class="row">
        <div class="col-xs-4 f_leave_ref_no_prefix">
            <div class="form-group addProduct_form">
                {!! Form::label('payroll_ref_no_prefix', __('essentials::lang.payroll_ref_no_prefix') . ':') !!}
                {!! Form::text(
                    'payroll_ref_no_prefix',
                    !empty($settings['payroll_ref_no_prefix']) ? $settings['payroll_ref_no_prefix'] : null,
                    ['class' => 'form-control', 'placeholder' => __('essentials::lang.payroll_ref_no_prefix')],
                ) !!}
            </div>
        </div>
    </div>
</div>
