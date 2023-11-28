<div class="pos-tab-content">
    <div class="row">
    @component('components.filters', ['title' => __('report.filters')])
   
   <div class="col-md-4">
           <div class="form-group ">
               {!! Form::label($id.'_created_by', __('report.user') . ':') !!}
               {!! Form::select($id.'_created_by', $sales_representative, null, [
                   'class' => 'form-control select2',
                   'style' => 'width:100%',
               ]) !!}
           </div>
       </div>
<div class="col-md-4">
   <div class="form-group">
       {!! Form::label($id.'_payment_method',  __('lang_v1.payment_method'). ':') !!}
       {!! Form::select($id.'_payment_method', $payment_types, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
   </div>
</div> 
<div class="col-md-4">
   <div class="form-group">
       {!! Form::label($id.'_date_range', __('report.date_range') . ':') !!}
       {!! Form::text($id.'_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control  ', 'readonly']); !!}
   </div>
</div> 
@endcomponent
    </div>

    <div class="row">
        <div class="col-md-12">
            

        <table class="table table-bordered table-striped" id="purchase_table">
            <thead>
                <tr>
                    <th>@lang('messages.action')</th>
                    <th>@lang('messages.date')</th>
                    <th>@lang('purchase.ref_no')</th>
                    <th>@lang('purchase.location')</th>
                    <th>@lang('purchase.supplier')</th>
                    <th>@lang('purchase.purchase_status')</th>
                    <th>@lang('purchase.payment_status')</th>
                    <th>@lang('purchase.grand_total')</th>
                    <th>@lang('purchase.payment_due') &nbsp;&nbsp;<i class="fa fa-info-circle text-info no-print" data-toggle="tooltip" data-placement="bottom" data-html="true" data-original-title="{{ __('messages.purchase_due_tooltip')}}" aria-hidden="true"></i></th>
                    <th>@lang('lang_v1.added_by')</th>
                </tr>
            </thead>
        </table>
        </div>
    </div>
</div>