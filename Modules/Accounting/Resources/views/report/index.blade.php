@extends('layouts.app')

@section('title', __('accounting::lang.journal_entry'))

@section('content')

    @include('accounting::layouts.nav')

   

    <section class="content" style="padding: 15px 5px">
         <!-- Content Header (Page header) -->
    <section class="content-header  f_content-header f_product_content-header">
        <h1>@lang('accounting::lang.reports')</h1>
    </section>
        <div class="row">
            <div class="col-md-4">
                <div class="box box-warning f_box" style="    min-height: 210px;">
                    <div class="box-header with-border" style="margin-bottom: 20px">
                        <h3 class="box-title trial_balance_title">@lang('accounting::lang.trial_balance')</h3>
                    </div>

                    <div class="box-body">
                        @lang('accounting::lang.trial_balance_description')
                        <br />
                        <a href="{{ route('accounting.trialBalance') }}" class="btn f_add-btn btn-sm pt-2"
                            style="width:fit-content;margin-top: 20px">

                            @lang('accounting::lang.view_report')
                            <svg width="5" height="8" viewBox="0 0 5 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M2.12956 4.00061L0.0078125 1.87961L1.06831 0.818359L4.25056 4.00061L1.06831 7.18286L0.0078125 6.12161L2.12956 4.00061Z"
                                    fill="white" />
                            </svg>
                        </a>
                    </div>

                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-warning f_box" style="    min-height: 210px;">
                    <div class="box-header with-border" style="margin-bottom: 20px">
                        <h3 class="box-title trial_balance_title">@lang('accounting::lang.ledger_report')</h3>
                    </div>

                    <div class="box-body">
                        @lang('accounting::lang.ledger_report_description')
                        <br />
                        <a @if ($ledger_url) href="{{ $ledger_url }}" @else onclick="alert(' @lang( 'accounting::lang.ledger_add_account') ')" @endif
                            class="btn f_add-btn btn-sm pt-2" style="width:fit-content;margin-top: 20px">
                            @lang('accounting::lang.view_report')
                            <svg width="5" height="8" viewBox="0 0 5 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M2.12956 4.00061L0.0078125 1.87961L1.06831 0.818359L4.25056 4.00061L1.06831 7.18286L0.0078125 6.12161L2.12956 4.00061Z"
                                    fill="white" />
                            </svg>


                        </a>
                    </div>

                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-warning f_box" style="    min-height: 210px;">
                    <div class="box-header with-border" style="margin-bottom: 20px">
                        <h3 class="box-title trial_balance_title">@lang('accounting::lang.balance_sheet')</h3>
                    </div>

                    <div class="box-body">
                        @lang('accounting::lang.balance_sheet_description')
                        <br />
                        <a href="{{ route('accounting.balanceSheet') }}" class="btn f_add-btn btn-sm pt-2"
                            style="width:fit-content;margin-top: 20px">@lang('accounting::lang.view_report')
                            <svg width="5" height="8" viewBox="0 0 5 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M2.12956 4.00061L0.0078125 1.87961L1.06831 0.818359L4.25056 4.00061L1.06831 7.18286L0.0078125 6.12161L2.12956 4.00061Z"
                                    fill="white" />
                            </svg>

                        </a>
                    </div>

                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-warning f_box" style="    min-height: 210px;">
                    <div class="box-header with-border" style="margin-bottom: 20px">
                        <h3 class="box-title trial_balance_title">@lang('accounting::lang.account_recievable_ageing_report')</h3>
                    </div>
                    <div class="box-body">
                        @lang('accounting::lang.account_recievable_ageing_report_description')
                        <br />
                        <a href="{{ route('accounting.account_receivable_ageing_report') }}"
                            class="btn f_add-btn btn-sm pt-2"
                            style="width:fit-content;margin-top: 20px">@lang('accounting::lang.view_report')
                            <svg width="5" height="8" viewBox="0 0 5 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M2.12956 4.00061L0.0078125 1.87961L1.06831 0.818359L4.25056 4.00061L1.06831 7.18286L0.0078125 6.12161L2.12956 4.00061Z"
                                    fill="white" />
                            </svg>

                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-warning f_box" style="    min-height: 210px;">
                    <div class="box-header with-border" style="margin-bottom: 20px">
                        <h3 class="box-title trial_balance_title">@lang('accounting::lang.account_payable_ageing_report')</h3>
                    </div>
                    <div class="box-body">
                        @lang('accounting::lang.account_payable_ageing_report_description')
                        <br />
                        <a href="{{ route('accounting.account_payable_ageing_report') }}"
                            class="btn f_add-btn btn-sm pt-2"
                            style="width:fit-content;margin-top: 20px">@lang('accounting::lang.view_report')
                            <svg width="5" height="8" viewBox="0 0 5 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M2.12956 4.00061L0.0078125 1.87961L1.06831 0.818359L4.25056 4.00061L1.06831 7.18286L0.0078125 6.12161L2.12956 4.00061Z"
                                    fill="white" />
                            </svg>

                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>

@stop
