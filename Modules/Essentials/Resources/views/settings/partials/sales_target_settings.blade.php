<div class="pos-tab-content" style="border-radius: 10px;padding: 26px;">
    <div class="row">
        <div class="col-xs-6 f_leave_ref_no_prefix">
            <div class="checkbox addProduct_form">
                <label>
                    {!! Form::checkbox('calculate_sales_target_commission_without_tax', 1, !empty($settings['calculate_sales_target_commission_without_tax']) ? 1 : 0, ['class' => 'input-icheck'] ); !!} @lang('essentials::lang.calculate_sales_target_commission_without_tax')
                </label>
                @show_tooltip(__('essentials::lang.calculate_sales_target_commission_without_tax_help'))
            </div>
        </div>
    </div>
</div>