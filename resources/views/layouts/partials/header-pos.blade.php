<!-- default value -->
@php
    $go_back_url = action([\App\Http\Controllers\SellPosController::class, 'index']);
    $transaction_sub_type = '';
    $view_suspended_sell_url = action([\App\Http\Controllers\SellController::class, 'index']) . '?suspended=1';
    $pos_redirect_url = action([\App\Http\Controllers\SellPosController::class, 'create']);
@endphp

@if (!empty($pos_module_data))
    @foreach ($pos_module_data as $key => $value)
        @php
            if (!empty($value['go_back_url'])) {
                $go_back_url = $value['go_back_url'];
            }
            
            if (!empty($value['transaction_sub_type'])) {
                $transaction_sub_type = $value['transaction_sub_type'];
                $view_suspended_sell_url .= '&transaction_sub_type=' . $transaction_sub_type;
                $pos_redirect_url .= '?sub_type=' . $transaction_sub_type;
            }
        @endphp
    @endforeach
@endif
<input type="hidden" name="transaction_sub_type" id="transaction_sub_type" value="{{ $transaction_sub_type }}">
@inject('request', 'Illuminate\Http\Request')
<div class="col-md-12 no-print pos-header" style="padding: 10px 28px !important;">
    <input type="hidden" id="pos_redirect_url" value="{{ $pos_redirect_url }}">
    <div class="row">
        <div class="col-md-6">
            <!--        move to pos page-->
            <div class="m-6 mt-5" style="display: flex;align-items: center">
                <p><strong>@lang('sale.location'): &nbsp;</strong>
                    @if (empty($transaction->location_id))
                        @if (count($business_locations) > 1)
                            <div style="width: 28%;margin-bottom: 5px;">
                                {!! Form::select(
                                    'select_location_id',
                                    $business_locations,
                                    $default_location->id ?? null,
                                    ['class' => 'form-control select2 input-sm', 'id' => 'select_location_id', 'required', 'autofocus'],
                                    $bl_attributes,
                                ) !!}
                            </div>
                        @else
                            {{ $default_location->name }}
                        @endif
                    @endif

                    @if (!empty($transaction->location_id))
                        {{ $transaction->location->name }}
                    @endif &nbsp;
                &nbsp;&nbsp;
                <span class="curr_datetime">{{ @format_datetime('now') }}</span> <i class="fa fa-keyboard hover-q text-muted" aria-hidden="true" data-container="body" data-toggle="popover" data-placement="bottom" data-content="@include('sale_pos.partials.keyboard_shortcuts_details')" data-html="true" data-trigger="hover" data-original-title="" title=""></i>
                </p>
            </div>
        </div>
        <div class="col-md-6">
            <a href="{{ $go_back_url }}" title="{{ __('lang_v1.go_back') }}"
                class="btn btn-info btn-flat m-6 btn-xs m-5 pull-right f_content-btn-pos"
                style="border-radius: 10px !important;background: #ECF2FF!important">
                <strong><i class="fa fa-backward fa-lg" style="font-size: 15px;color: #0038FF;"></i></strong>
            </a>
            @if (!empty($pos_settings['inline_service_staff']))
                <button type="button" id="show_service_staff_availability"
                    title="{{ __('lang_v1.service_staff_availability') }}"
                    class="btn btn-primary btn-flat m-6 btn-xs m-5 pull-right"  data-container=".view_modal"
                    data-href="{{ action([\App\Http\Controllers\SellPosController::class, 'showServiceStaffAvailibility']) }}">
                    <strong><i class="fa fa-users fa-lg" > </i></strong>
                </button>
            @endif

            @can('close_cash_register')
                <button type="button" id="close_register" title="{{ __('cash_register.close_register') }}"
                    class="btn btn-danger btn-flat m-6 btn-xs m-5 btn-modal pull-right f_content-btn-pos"
                    style="border-radius: 10px !important ; background:#EF1463 !important"
                    data-container=".close_register_modal"
                    data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getCloseRegister']) }}">
                    <strong><i class="fa fa-window-close fa-lg" style="font-size: 15px"></i></strong>
                </button>
            @endcan

            @can('view_cash_register')
                <button type="button" id="register_details" title="{{ __('cash_register.register_details') }}"
                    class="btn btn-success btn-flat m-6 btn-xs m-5 btn-modal pull-right f_content-btn-pos"
                    style="background: #E9FFD8 !important;
                    border-radius: 10px !important;"
                    data-container=".register_details_modal"
                    data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getRegisterDetails']) }}">
                    <strong><i class="fa fa-briefcase fa-lg" style="font-size: 15px;color: #05A44E;"
                            aria-hidden="true"></i></strong>
                </button>
            @endcan

            <button title="@lang('lang_v1.calculator')" id="btnCalculator" type="button"
                class="btn btn-success btn-flat pull-right m-5 btn-xs mt-10 popover-default f_content-btn-pos"
                style="border-radius: 10px !important;background:  #FFEEE7 !important" data-toggle="popover"
                data-trigger="click" data-content='@include('layouts.partials.calculator')' data-html="true"
                data-placement="bottom">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="margin-bottom: 5px"
                    xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_83_2451)">
                        <path
                            d="M4 2H20C20.2652 2 20.5196 2.10536 20.7071 2.29289C20.8946 2.48043 21 2.73478 21 3V21C21 21.2652 20.8946 21.5196 20.7071 21.7071C20.5196 21.8946 20.2652 22 20 22H4C3.73478 22 3.48043 21.8946 3.29289 21.7071C3.10536 21.5196 3 21.2652 3 21V3C3 2.73478 3.10536 2.48043 3.29289 2.29289C3.48043 2.10536 3.73478 2 4 2ZM5 4V20H19V4H5ZM7 6H17V10H7V6ZM7 12H9V14H7V12ZM7 16H9V18H7V16ZM11 12H13V14H11V12ZM11 16H13V18H11V16ZM15 12H17V18H15V12Z"
                            fill="#FF8552"></path>
                    </g>
                    <defs>
                        <clipPath id="clip0_83_2451">
                            <rect width="24" height="24" fill="white"></rect>
                        </clipPath>
                    </defs>
                </svg>
                <strong style="color: #000000; !important">
                    Calculator
                </strong>
            </button>
            <!--this btn move to pos page-->
            {{--      <button type="button" class="btn btn-danger btn-flat m-6 btn-xs m-5 pull-right popover-default" id="return_sale" title="@lang('lang_v1.sell_return')" data-toggle="popover" data-trigger="click" data-content='<div class="m-8"><input type="text" class="form-control" placeholder="@lang("sale.invoice_no")" id="send_for_sell_return_invoice_no"></div><div class="w-100 text-center"><button type="button" class="btn btn-danger" id="send_for_sell_return">@lang("lang_v1.send")</button></div>' data-html="true" data-placement="bottom"> --}}
            {{--            <strong><i class="fas fa-undo fa-lg"></i></strong> --}}
            {{--      </button> --}}

            <button type="button" title="{{ __('lang_v1.full_screen') }}"
                class="btn btn-primary btn-flat m-6 hidden-xs btn-xs m-5 pull-right f_content-btn-pos"
                style="border-radius: 10px !important;background: rgba(0, 56, 255, 0.44) !important" id="full_screen">
                <strong><i class="fa fa-window-maximize fa-lg" style="font-size: 15px;margin-bottom: 5px;"></i></strong>
            </button>

            <button type="button" id="view_suspended_sales" title="{{ __('lang_v1.view_suspended_sales') }}"
                class="btn bg-yellow f_content-btn-pos btn-flat m-6 btn-xs m-5 btn-modal pull-right"
                style="border-radius: 10px !important" data-container=".view_modal"
                data-href="{{ $view_suspended_sell_url }}">
                <i class="fa fa-pause-circle fa-lg" style="font-size: 15px;margin-bottom: 5px;"></i>
                {{-- <strong>
                  suspended sell
                </strong> --}}
            </button>
            @if (empty($pos_settings['hide_product_suggestion']) && isMobile())
                <button type="button" title="{{ __('lang_v1.view_products') }}" data-placement="bottom"
                        style="border-radius: 10px !important"
                    class="btn btn-success btn-flat m-6 btn-xs m-5 btn-modal pull-right f_content-btn-pos" data-toggle="modal"
                    data-target="#mobile_product_suggestion_modal">
                    <strong><i class="fa fa-cubes fa-lg"></i></strong>
                </button>
            @endif

            @if (Module::has('Repair') && $transaction_sub_type != 'repair')
                @include('repair::layouts.partials.pos_header')
            @endif

            @if (in_array('pos_sale', $enabled_modules) && !empty($transaction_sub_type))
                @can('sell.create')
                    <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}"
                       style="border-radius: 10px !important"
                        title="@lang('sale.pos_sale')" class="btn btn-success btn-flat m-6 btn-xs m-5 pull-right f_content-btn-pos">
                        <strong><i class="fa fa-th-large"></i> &nbsp; @lang('sale.pos_sale')</strong>
                    </a>
                @endcan
            @endif
            @can('expense.add')
                <button type="button" title="{{ __('expense.add_expense') }}" data-placement="bottom"
                    class="btn  btn-flat m-6 btn-xs m-5 btn-modal pull-right f_content-btn-pos"
                    style="border-radius: 10px !important;background-color: #e4e4e8" id="add_expense">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="margin-bottom: 5px"
                        xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_83_2437)">
                            <path
                                d="M7 8V6C7 4.67392 7.52678 3.40215 8.46447 2.46447C9.40215 1.52678 10.6739 1 12 1C13.3261 1 14.5979 1.52678 15.5355 2.46447C16.4732 3.40215 17 4.67392 17 6V8H20C20.2652 8 20.5196 8.10536 20.7071 8.29289C20.8946 8.48043 21 8.73478 21 9V21C21 21.2652 20.8946 21.5196 20.7071 21.7071C20.5196 21.8946 20.2652 22 20 22H4C3.73478 22 3.48043 21.8946 3.29289 21.7071C3.10536 21.5196 3 21.2652 3 21V9C3 8.73478 3.10536 8.48043 3.29289 8.29289C3.48043 8.10536 3.73478 8 4 8H7ZM7 10H5V20H19V10H17V12H15V10H9V12H7V10ZM9 8H15V6C15 5.20435 14.6839 4.44129 14.1213 3.87868C13.5587 3.31607 12.7956 3 12 3C11.2044 3 10.4413 3.31607 9.87868 3.87868C9.31607 4.44129 9 5.20435 9 6V8Z"
                                fill="#49487A"></path>
                        </g>
                        <defs>
                            <clipPath id="clip0_83_2437">
                                <rect width="24" height="24" fill="white"></rect>
                            </clipPath>
                        </defs>
                    </svg>
                    <strong> @lang('expense.add_expense') </strong>
                </button>
            @endcan

        </div>

    </div>
</div>
