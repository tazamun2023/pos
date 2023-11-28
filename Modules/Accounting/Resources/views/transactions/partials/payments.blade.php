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
            <!-- <div class="box"> -->
                <div class="box-body">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="{{$id}}">
                        <thead>
                            <tr>
                                <th>@lang('messages.action')</th>
                                <th>@lang('messages.date')</th>
                                <th>@lang('account.payment_ref_no')</th>
                                <th>@lang('account.invoice_ref_no')</th>
                                <th>@lang('sale.amount')</th>
                                <th>@lang('lang_v1.payment_type')</th>
                                <th>@lang( 'lang_v1.description' )</th>
                            </tr>
                        </thead>
                    </table>
                    </div>
                </div>
            <!-- </div> -->
        </div>
    </div>

</div>