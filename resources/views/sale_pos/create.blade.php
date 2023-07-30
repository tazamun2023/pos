@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('content')
    <section class="content no-print">
        <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
        @if (!empty($pos_settings['allow_overselling']))
            <input type="hidden" id="is_overselling_allowed">
        @endif
        @if (session('business.enable_rp') == 1)
            <input type="hidden" id="reward_point_enabled">
        @endif
        @php
            $is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
            $is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
        @endphp
        {!! Form::open([
            'url' => action([\App\Http\Controllers\SellPosController::class, 'store']),
            'method' => 'post',
            'id' => 'add_pos_sell_form',
        ]) !!}
        <div class="row mb-12">
            <div class="col-md-12">
                <div class="row">
                    @if (empty($pos_settings['hide_product_suggestion']) && !isMobile())
                        <div class="col-md-6 no-padding">
                            <div class="f_pos_products" style="margin-left:  25px">
                                @include('sale_pos.partials.pos_sidebar')
                            </div>
                            <div class='f_pos_note_div'>
                                {!! Form::label('sell_note', __('sale.sell_note')) !!}
                                {!! Form::textarea('sale_note_pos', null, [
                                    'class' => 'form-control f_pos_note_textarea',
                                    'placeholder' => __('sale.note'),
                                    'rows' => 8,
                                ]) !!}
                            </div>
                        </div>
                    @endif
                    <div class="@if (empty($pos_settings['hide_product_suggestion'])) col-md-6 @else col-md-10 col-md-offset-1 @endif no-padding "
                        style='padding:0 25px !important'>
                        <div class="box box-solid f_pos_products  mb-12">
                            <div class="box-body pb-0" style="padding:  0 !important">

                                {!! Form::hidden('location_id', $default_location->id ?? null, [
                                    'id' => 'location_id',
                                    'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                                        ? $default_location->receipt_printer_type
                                        : 'browser',
                                    'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '',
                                ]) !!}
                                <!-- sub_type -->
                                {!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
                                <input type="hidden" id="item_addition_method"
                                    value="{{ $business_details->item_addition_method }}">
                                @include('sale_pos.partials.pos_form')

                                @include('sale_pos.partials.pos_form_totals')

                                @include('sale_pos.partials.payment_modal')

                                @if (empty($pos_settings['disable_suspend']))
                                    @include('sale_pos.partials.suspend_note_modal')
                                @endif

                                @if (empty($pos_settings['disable_recurring_invoice']))
                                    @include('sale_pos.partials.recurring_invoice_modal')
                                @endif
                            </div>
                            @php
                                $is_mobile = isMobile();
                            @endphp
                            @if (!$is_mobile)
                                <div class="bg-navy pos-total text-white"
                                    style="display: flex;
								align-items: center;
								gap: 20px;border-radius: 8px;">
                                    <span class="text">@lang('sale.total_payable')</span>
                                    <input type="hidden" name="final_total" id="final_total_input" value=0>
                                    <span id="total_payable" class="number">0</span>
                                </div>
                            @endif
                            <div class='f_action_payment'>
                                <h3>Payment Method</h3>
                                <div>
                                    <div class="row">
                                        <div class="pos-form-actions">
                                            <div class='pos-form-actions_btn_group'>
                                                @if ($is_mobile)
                                                    <div class="col-md-12 text-right">
                                                        <b>@lang('sale.total_payable'):</b>
                                                        <input type="hidden" name="final_total" id="final_total_input"
                                                            value=0>
                                                        <span id="total_payable"
                                                            class="text-success lead text-bold text-right">0</span>
                                                    </div>
                                                @endif
                                                <button type="button"
                                                    class="@if ($is_mobile) col-xs-6 @endif btn bg-info text-white btn-default btn-flat @if ($pos_settings['disable_draft'] != 0) hide @endif pos-form-actions_btn"
                                                    id="pos-draft" @if (!empty($only_payment)) disabled @endif><svg
                                                        height="30px" viewBox="0 0 128 128" width="30px"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <g>
                                                            <path
                                                                d="m104.839 105.618a5.307 5.307 0 0 0 5.306-5.306v-60.012h-86.257l-16.924 40.657v13.331a11.341 11.341 0 0 0 11.342 11.33z"
                                                                fill="#ecebfd" />
                                                            <path d="m36.661 84.199h11.281v11.281h-11.281z"
                                                                fill="#fdcb71" />
                                                            <path d="m47.942 75.938h11.281v19.542h-11.281z"
                                                                fill="#ff7a8c" />
                                                            <path d="m59.224 79.847h11.281v15.634h-11.281z"
                                                                fill="#fdcb71" />
                                                            <path d="m70.505 73.451h11.281v22.03h-11.281z" fill="#ff7a8c" />
                                                            <g fill="#696eaf">
                                                                <path
                                                                    d="m89.514 52.816a1.75 1.75 0 0 0 0-3.5h-53.771a1.75 1.75 0 1 0 0 3.5z" />
                                                                <path
                                                                    d="m73.525 61.937a1.75 1.75 0 0 0 0-3.5h-37.782a1.75 1.75 0 0 0 0 3.5z" />
                                                                <path
                                                                    d="m120.58 30.162-10.822-5.361-7.672 15.498-10.258 20.726 10.822 5.362 7.495-15.145z" />
                                                            </g>
                                                            <path
                                                                d="m120.58 30.162a4.337 4.337 0 0 0 -1.968-5.826l-3.018-1.5a4.361 4.361 0 0 0 -5.836 1.968l-2.877 5.812 10.819 5.356z"
                                                                fill="#d1d1fd" />
                                                            <path d="m90.277 77.768 12.376-11.384-10.827-5.36z"
                                                                fill="#d1d1fd" />
                                                            <path
                                                                d="m101.77 56.3a1.751 1.751 0 0 0 1.57-.974l6.187-12.5a1.75 1.75 0 1 0 -3.136-1.553l-6.188 12.5a1.751 1.751 0 0 0 1.567 2.527z"
                                                                fill="#4c5696" />
                                                            <path
                                                                d="m23.888 82.935v-50.684h-5.582a11.342 11.342 0 0 0 -11.342 11.331v50.706a11.38 11.38 0 0 1 11.342-11.353z"
                                                                fill="#d1d1fd" />
                                                        </g>
                                                    </svg> @lang('sale.draft')</button>
                                                <button type="button"
                                                    class="pos-form-actions_btn btn btn-default bg-yellow btn-flat @if ($is_mobile) col-xs-6 @endif"
                                                    id="pos-quotation"
                                                    @if (!empty($only_payment)) disabled @endif><svg height="30px"
                                                        viewBox="0 0 512 511" width="30px"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="m362.667969 512.484375h-298.667969c-35.285156 0-64-28.714844-64-64v-298.667969c0-35.285156 28.714844-64 64-64h170.667969c11.796875 0 21.332031 9.558594 21.332031 21.335938 0 11.773437-9.535156 21.332031-21.332031 21.332031h-170.667969c-11.777344 0-21.332031 9.578125-21.332031 21.332031v298.667969c0 11.753906 9.554687 21.332031 21.332031 21.332031h298.667969c11.773437 0 21.332031-9.578125 21.332031-21.332031v-170.667969c0-11.773437 9.535156-21.332031 21.332031-21.332031s21.335938 9.558594 21.335938 21.332031v170.667969c0 35.285156-28.714844 64-64 64zm0 0"
                                                            fill="#607d8b" />
                                                        <g fill="#42a5f5">
                                                            <path
                                                                d="m368.8125 68.261719-168.792969 168.789062c-1.492187 1.492188-2.496093 3.390625-2.921875 5.4375l-15.082031 75.4375c-.703125 3.496094.40625 7.101563 2.921875 9.640625 2.027344 2.027344 4.757812 3.113282 7.554688 3.113282.679687 0 1.386718-.0625 2.089843-.210938l75.414063-15.082031c2.089844-.429688 3.988281-1.429688 5.460937-2.925781l168.789063-168.789063zm0 0" />
                                                            <path
                                                                d="m496.382812 16.101562c-20.796874-20.800781-54.632812-20.800781-75.414062 0l-29.523438 29.523438 75.414063 75.414062 29.523437-29.527343c10.070313-10.046875 15.617188-23.445313 15.617188-37.695313s-5.546875-27.648437-15.617188-37.714844zm0 0" />
                                                        </g>
                                                    </svg> @lang('lang_v1.quotation')</button>

                                                @if (empty($pos_settings['disable_suspend']))
                                                    <button type="button"
                                                        class="pos-form-actions_btn @if ($is_mobile) col-xs-6 @endif btn bg-red btn-default btn-flat no-print pos-express-finalize"
                                                        data-pay_method="suspend" title="@lang('lang_v1.tooltip_suspend')"
                                                        @if (!empty($only_payment)) disabled @endif>
                                                        <svg id="color" enable-background="new 0 0 24 24" height="30px"
                                                            viewBox="0 0 24 24" width="30px"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="m11.75 10h-7.5c-.965 0-1.75-.785-1.75-1.75v-2.75c0-3.033 2.467-5.5 5.5-5.5s5.5 2.467 5.5 5.5v2.75c0 .965-.785 1.75-1.75 1.75z"
                                                                fill="#455a64" />
                                                            <path
                                                                d="m11.5 6.5c-2.333 0-3.5-1.75-3.5-3.5 0 1.75-1.167 3.5-3.5 3.5 0 1.937 1.563 3.5 3.5 3.5s3.5-1.563 3.5-3.5z"
                                                                fill="#90a4ae" />
                                                            <path
                                                                d="m17.5 11c-3.584 0-6.5 2.916-6.5 6.5s2.916 6.5 6.5 6.5 6.5-2.916 6.5-6.5-2.916-6.5-6.5-6.5zm0 1.5c1.108 0 2.123.374 2.953.987l-6.967 6.966c-.612-.83-.986-1.845-.986-2.953 0-2.757 2.243-5 5-5zm0 10c-1.108 0-2.123-.374-2.953-.987l6.967-6.966c.612.83.986 1.845.986 2.953 0 2.757-2.243 5-5 5z"
                                                                fill="#f44336" />
                                                            <path
                                                                d="m11.706 12h-7.956c-2.068 0-3.75 1.682-3.75 3.75v3.5c0 .414.336.75.75.75h9.156c-.938-2.839-.216-5.877 1.8-8z"
                                                                fill="#90a4ae" />
                                                            <path
                                                                d="m22.181 12.995c-.024-.033-.051-.065-.08-.095l-.006.006c-3.065 3.065-6.124 6.124-9.188 9.188l-.007.006c.03.03.061.056.095.08 1.169 1.127 2.757 1.82 4.505 1.82 3.584 0 6.5-2.916 6.5-6.5 0-1.747-.693-3.336-1.819-4.505zm-4.681 9.505c-1.109 0-2.134-.363-2.964-.976l6.989-6.988c.613.83.975 1.855.975 2.964 0 2.757-2.243 5-5 5z"
                                                                fill="#d43a2f" />
                                                            <path
                                                                d="m8 0c-3.033 0-5.5 2.467-5.5 5.5v2.75c0 .965.785 1.75 1.75 1.75h3.753c-.001 0-.002 0-.003 0-1.937 0-3.5-1.563-3.5-3.5 2.333 0 3.5-1.75 3.5-3.5 0 .055.001.109.003.164v-3.164c-.001 0-.002 0-.003 0z"
                                                                fill="#3c4e57" />
                                                            <g fill="#7d8f97">
                                                                <path
                                                                    d="m8 3c0 1.75-1.167 3.5-3.5 3.5 0 1.937 1.563 3.5 3.5 3.5h.003v-6.836c-.002-.055-.003-.109-.003-.164z" />
                                                                <path
                                                                    d="m8.003 12h-4.253c-2.068 0-3.75 1.682-3.75 3.75v3.5c0 .414.336.75.75.75h7.253z" />
                                                            </g>
                                                        </svg>
                                                        @lang('lang_v1.suspend')
                                                    </button>
                                                @endif

                                                @if (empty($pos_settings['disable_credit_sale_button']))
                                                    <input type="hidden" name="is_credit_sale" value="0"
                                                        id="is_credit_sale">
                                                    <button type="button"
                                                        class="pos-form-actions_btn btn bg-purple btn-default btn-flat no-print pos-express-finalize @if ($is_mobile) col-xs-6 @endif"
                                                        data-pay_method="credit_sale" title="@lang('lang_v1.tooltip_credit_sale')"
                                                        @if (!empty($only_payment)) disabled @endif>
                                                        <svg width="30px" height="30px" viewBox="0 0 48 48"
                                                            fill="none" xmlns="http://www.w3.org/2000/svg"
                                                            xmlns:xlink="http://www.w3.org/1999/xlink">
                                                            <rect width="48" height="48" fill="url(#pattern0)" />
                                                            <defs>
                                                                <pattern id="pattern0"
                                                                    patternContentUnits="objectBoundingBox" width="1"
                                                                    height="1">
                                                                    <use xlink:href="#image0_350_16892"
                                                                        transform="scale(0.0208333)" />
                                                                </pattern>
                                                                <image id="image0_350_16892" width="48"
                                                                    height="48"
                                                                    xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAAEPElEQVR4nO1Z609cRRS/8fFBv/n64uOvMDb2E99MkxmYWeoFC8YaG03U2qixsfYLjX2I8dHYNDSE1Bq/uc3OLLvbWg2lpD5ZMQqFlhaauuu2BFiWWMTlseWYgyxwe6l7z9y7VJP9JZOQnGXm9zszZ86Zcy2rggoq8A07bN/JldzItNjNtYgwJQe4kjmuxdziUDLHlOxfsr1THQk92dTUdId1uyG0eIwr2cyVzHAtgTbE70yL9zZFQo+uO/FNYfshrmUr02KWTtw52OIcokUq+cC6kOdaNHAtJvwSX0NIlmtRXzbij7e+dDdTsi1o4twt5EjVmaq7AiXPY/xepsXJcpPnK/GRwDWD87wh+SJMRVQFsRN+jo0/ARJFtPgjHwk1+jkK/gVIqFayzog8XmtMyXHqgttPvw6d6TOQzWeXBcwUZmAwdxHa+j6FULtNDeosj/EHyQLwnqeSP9DdDLOFOfg3ZKYy8MJXL1JFHCaRx+xITVJIKl/IgxcMTQ5DtQ55F6HkDIuzR7x7H8sDovf1ULuLaO9YH8Qvn4Arf/zmsr37w35qQB/wRB6LLKxTqALS19MOgtHh2LJNtm+G7pGkw96Z7qIJUDKDRaMX7280uS1mC7MOglu/3Oawv9m186ZYuEpegym5oaSAf0piugC8aVbDjm9x2OsTDQ57fj5PXoMrucvLDigTAanrKQfB5uQHdIK6pIDjpXdg8eFBn/yLweMOAdPz03Dw50NQE60NUkBf6R0wLJW3nHgWxv9aSV5FXPtzBA7/0kJOYHztGBj3EgPGj5RXOna4bqMiMDO39rb52xElZ8oqAEdtrA5ilxOwsLCwppDzExeg8eRz5RPAA3ptvdzxGnx79TtYALeQS5NDUBuzy3SEDIP4VuPV0zsgOfKTS0Rb31GTHfAUxJEgBeDAugcz72pczF0ymEuEy5LIbs7C9YlG12/e6HrLdc1y6hHS4u2SArDpRJ14dHrUQQ7JumNiu+M3WHZz6g5EQk94KuaYkmnKxGcz3zjIdaQ6Xb/5bOBzX7UQUzLluZuHHTPK5Hu+3+sK0h+vdcNHPQfh/eSHcOrK1zB/o+Cwtw/Had5Xcr9VrgcNBmlypAe8YmpuCp4/tY1CnvagQWBHgOKhungD/DrWW5J8Lj8JO8/uonr/kEXFU2H7fq7FGGWh6mgIPu75BPqzAzB3Y+VtjH9fmBiEo+eOucpsXursa5E17puySOgZ8k2x6hVWhIhuNpqDo4BozdNG5JdFaHHEdPEiTP+fmxydW3y8UOstgGkRD6zJi43WxYarqSep5JWM2WH7HitIoDeoN5PpsakKur2+GtirNGk5ejgyo74DlnLFYrsPE4x/8iKPXmcJdp+13sDsiB0zau20dM5TTIt9NdGah63bjaUCcAP2bbD1gQ8PfNlhObL0EW+CKdmLNiyJsar8T3xmraAC6/+PvwH8Cssr5J01hAAAAABJRU5ErkJggg==" />
                                                            </defs>
                                                        </svg> @lang('lang_v1.credit_sale')
                                                    </button>
                                                @endif
                                                <button type="button"
                                                    class="pos-form-actions_btn btn bg-maroon btn-default btn-flat no-print @if (!empty($pos_settings['disable_suspend']))  @endif pos-express-finalize @if (!array_key_exists('card', $payment_types)) hide @endif @if ($is_mobile) col-xs-6 @endif"
                                                    data-pay_method="card" title="@lang('lang_v1.tooltip_express_checkout_card')">
                                                    <svg id="Layer_1" height="30px" viewBox="0 0 487.5 361.2"
                                                        width="30px" xmlns="http://www.w3.org/2000/svg">
                                                        <g id="Layer_16">
                                                            <g>
                                                                <path
                                                                    d="m437.8 332.3 31.9-153.8 17.4-84.2c2-10-4.4-19.8-14.4-22l-347.2-71.9c-10.1-2.1-19.9 4.4-22 14.4l-17.5 84.2-31.8 153.8c-2.1 10.1 4.4 19.9 14.4 22l347.2 71.9c10.1 2.1 19.9-4.4 22-14.4z"
                                                                    fill="#49699c" />
                                                                <path d="m95.9 51.5 383.8 78.7-10 48.3-384.8-73.8z"
                                                                    fill="#e6e7e8" />
                                                                <path
                                                                    d="m373.2 81h-354.6c-10.3 0-18.6 8.3-18.6 18.6v243c0 10.3 8.3 18.6 18.6 18.6h354.6c10.3 0 18.6-8.3 18.6-18.6v-243c0-10.3-8.3-18.6-18.6-18.6z"
                                                                    fill="#5ebfd2" />
                                                                <path
                                                                    d="m50.4 137.3c-.1-3.8 2.9-7 6.7-7.1h28.9c3.8.1 6.8 3.3 6.7 7.1v21.6c.1 3.8-2.8 6.9-6.6 7h-.1-28.9c-3.8-.1-6.8-3.1-6.7-6.9v-.1z"
                                                                    fill="#feba26" />
                                                                <path
                                                                    d="m86 171.9h-28.9c-7.1-.1-12.8-5.8-12.7-12.9v-.1-21.6c-.1-7.1 5.6-13 12.7-13.1h28.9c7.1.1 12.8 6 12.7 13.1v21.6c.1 7.1-5.5 12.9-12.7 13zm-28.9-35.7c-.3 0-.7.4-.7 1.1v21.6c0 .6.4 1 .7 1h28.9c.3 0 .7-.4.7-1v-21.6c0-.7-.4-1.1-.7-1.1z"
                                                                    fill="#e6e7e8" />
                                                                <path
                                                                    d="m319.7 127.1h-12v-17.9h12zm24.6-17.9h-12v17.9h12zm-77.9 0h-12v17.9h12zm24.6 0h-12v17.9h12zm-244.8 172.1h-17.8v12h17.8zm29.1 12h-17.8v-12h17.8zm29.6 0h-17.9v-12h17.9zm29.1 0h-17.9v-12h17.9zm29.6 0h-17.9v-12h17.9zm0 39.3h-135.2v-12h135.2z"
                                                                    fill="#49699c" />
                                                            </g>
                                                        </g>
                                                    </svg> @lang('lang_v1.express_checkout_card')
                                                </button>

                                                <button type="button"
                                                    class="pos-form-actions_btn btn bg-navy btn-default @if (!$is_mobile)  @endif btn-flat no-print @if ($pos_settings['disable_pay_checkout'] != 0) hide @endif @if ($is_mobile) col-xs-6 @endif"
                                                    id="pos-finalize" title="@lang('lang_v1.tooltip_checkout_multi_pay')"><svg version="1.1"
                                                        id="svg1248" xml:space="preserve" width="30px"
                                                        height="30px" viewBox="0 0 682.66669 682.66669"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        xmlns:svg="http://www.w3.org/2000/svg">
                                                        <defs id="defs1252">
                                                            <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1262">
                                                                <path d="M 0,512 H 512 V 0 H 0 Z" id="path1260" />
                                                            </clipPath>
                                                        </defs>
                                                        <g id="g1254"
                                                            transform="matrix(1.3333333,0,0,-1.3333333,0,682.66667)">
                                                            <g id="g1256">
                                                                <g id="g1258" clip-path="url(#clipPath1262)">
                                                                    <g id="g1264"
                                                                        transform="translate(496.6807,9.7827)">
                                                                        <path
                                                                            d="m 0,0 h -206.686 c -4.318,0 -7.819,3.501 -7.819,7.82 v 130.26 c 0,4.319 3.501,7.82 7.819,7.82 L 0,145.9 c 4.318,0 7.819,-3.501 7.819,-7.82 V 7.82 C 7.819,3.501 4.318,0 0,0"
                                                                            style="fill:#9cd169;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                                            id="path1266" />
                                                                    </g>
                                                                    <g id="g1268"
                                                                        transform="translate(222.0049,9.7827)">
                                                                        <path
                                                                            d="m 0,0 h -206.686 c -4.318,0 -7.819,3.501 -7.819,7.82 v 130.26 c 0,4.319 3.501,7.82 7.819,7.82 L 0,145.9 c 4.318,0 7.819,-3.501 7.819,-7.82 V 7.82 C 7.819,3.501 4.318,0 0,0"
                                                                            style="fill:#ddebfd;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                                            id="path1270" />
                                                                    </g>
                                                                    <g id="g1272"
                                                                        transform="translate(222.0049,155.6826)">
                                                                        <path
                                                                            d="m 0,0 h -25.001 c 4.319,0 7.82,-3.501 7.82,-7.82 v -130.26 c 0,-4.319 -3.501,-7.82 -7.82,-7.82 H 0 c 4.318,0 7.819,3.501 7.819,7.82 V -7.82 C 7.819,-3.501 4.318,0 0,0"
                                                                            style="fill:#bed9fd;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                                            id="path1274" />
                                                                    </g>
                                                                    <g id="g1276"
                                                                        transform="translate(158.6738,103.3608)">
                                                                        <path
                                                                            d="m 0,0 14.928,-14.928 c 2.733,-2.734 7.166,-2.734 9.899,0 L 39.755,0 c 2.734,2.734 2.734,7.166 0,9.899 L 24.827,24.828 c -2.733,2.733 -7.166,2.733 -9.899,0 L 0,9.899 C -2.734,7.166 -2.734,2.734 0,0"
                                                                            style="fill:#e175a5;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                                            id="path1278" />
                                                                    </g>
                                                                    <g id="g1280"
                                                                        transform="translate(496.6807,155.6826)">
                                                                        <path
                                                                            d="m 0,0 h -25 c 4.318,0 7.819,-3.501 7.819,-7.82 v -130.26 c 0,-4.319 -3.501,-7.82 -7.819,-7.82 H 0 c 4.318,0 7.819,3.501 7.819,7.82 V -7.82 C 7.819,-3.501 4.318,0 0,0"
                                                                            style="fill:#7dc03a;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                                            id="path1282" />
                                                                    </g>
                                                                    <g id="g1284"
                                                                        transform="translate(328.0459,155.6826)">
                                                                        <path
                                                                            d="m 0,0 h -38.051 c -4.318,0 -7.819,-3.501 -7.819,-7.82 V -45.87 C -20.537,-45.87 0,-25.333 0,0"
                                                                            style="fill:#ddebfd;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                                            id="path1286" />
                                                                    </g>
                                                                    <g id="g1288"
                                                                        transform="translate(504.5,17.6025)">
                                                                        <path
                                                                            d="m 0,0 v 38.05 c -25.333,0 -45.869,-20.537 -45.869,-45.87 h 38.05 C -3.501,-7.82 0,-4.319 0,0"
                                                                            style="fill:#ddebfd;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                                            id="path1290" />
                                                                    </g>
                                                                    <g id="g1292"
                                                                        transform="translate(504.5,109.813)">
                                                                        <path
                                                                            d="m 0,0 v 38.05 c 0,4.319 -3.501,7.82 -7.819,7.82 h -38.05 C -45.869,20.537 -25.333,0 0,0"
                                                                            style="fill:#ddebfd;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                                            id="path1294" />
                                                                    </g>
                                                                    <g id="g1296"
                                                                        transform="translate(282.1758,55.6523)">
                                                                        <path
                                                                            d="m 0,0 v -38.05 c 0,-4.319 3.501,-7.82 7.819,-7.82 H 45.87 C 45.87,-20.537 25.333,0 0,0"
                                                                            style="fill:#ddebfd;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                                            id="path1298" />
                                                                    </g>
                                                                    <g id="g1300"
                                                                        transform="translate(479.5,17.6025)">
                                                                        <path
                                                                            d="m 0,0 c 0,-4.319 -3.501,-7.82 -7.819,-7.82 h 25 c 2.968,0 5.551,1.655 6.876,4.092 0.24,0.444 0.439,0.913 0.591,1.402 C 24.877,-1.591 25,-0.81 25,0 v 38.05 c -9.225,0 -17.807,-2.733 -25,-7.42 z"
                                                                            style="fill:#bed9fd;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                                            id="path1302" />
                                                                    </g>
                                                                    <g id="g1304"
                                                                        transform="translate(503.5566,151.5903)">
                                                                        <path
                                                                            d="m 0,0 c -1.324,2.438 -3.907,4.092 -6.876,4.092 h -25 c 4.318,0 7.819,-3.501 7.819,-7.82 v -30.63 c 7.194,-4.686 15.776,-7.419 25,-7.419 V -3.728 C 0.943,-2.378 0.602,-1.108 0,0"
                                                                            style="fill:#bed9fd;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                                            id="path1306" />
                                                                    </g>
                                                                    <g id="g1308"
                                                                        transform="translate(329.7705,311.5693)">
                                                                        <path
                                                                            d="m 0,0 h -147.541 c -8.31,0 -14.543,7.6 -12.918,15.749 l 25.657,128.662 c 1.084,5.434 5.854,9.346 11.395,9.346 h 99.273 c 5.541,0 10.311,-3.912 11.395,-9.346 L 12.918,15.749 C 14.543,7.6 8.31,0 0,0"
                                                                            style="fill:#e175a5;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                                            id="path1310" />
                                                                    </g>
                                                                    <g id="g1312"
                                                                        transform="translate(342.6885,327.3179)">
                                                                        <path
                                                                            d="m 0,0 -25.657,128.662 c -1.083,5.434 -5.854,9.347 -11.395,9.347 h -25 c 5.541,0 10.311,-3.913 11.395,-9.347 L -25,0 c 1.625,-8.149 -4.608,-15.749 -12.918,-15.749 h 25 c 8.31,0 14.543,7.6 12.918,15.749"
                                                                            style="fill:#de5791;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                                            id="path1314" />
                                                                    </g>
                                                                    <g id="g1316"
                                                                        transform="translate(229.8242,99.2573)">
                                                                        <path
                                                                            d="m 0,0 v -81.655 c 0,-4.319 -3.501,-7.82 -7.819,-7.82 h -206.686 c -4.318,0 -7.819,3.501 -7.819,7.82 v 130.26 c 0,4.319 3.501,7.82 7.819,7.82 H -7.819 C -3.501,56.425 0,52.924 0,48.605 Z"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1318" />
                                                                    </g>
                                                                    <g id="g1320"
                                                                        transform="translate(158.6738,103.3608)">
                                                                        <path
                                                                            d="m 0,0 14.928,-14.928 c 2.733,-2.734 7.166,-2.734 9.899,0 L 39.755,0 c 2.734,2.734 2.734,7.166 0,9.899 L 24.827,24.828 c -2.733,2.733 -7.166,2.733 -9.899,0 L 0,9.899 C -2.734,7.166 -2.734,2.734 0,0 Z"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1322" />
                                                                    </g>
                                                                    <g id="g1324"
                                                                        transform="translate(282.1758,109.813)">
                                                                        <path d="M 0,0 C 25.333,0 45.87,20.537 45.87,45.87"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1326" />
                                                                    </g>
                                                                    <g id="g1328"
                                                                        transform="translate(504.5,55.6523)">
                                                                        <path
                                                                            d="M 0,0 C -25.333,0 -45.869,-20.537 -45.869,-45.87"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1330" />
                                                                    </g>
                                                                    <g id="g1332"
                                                                        transform="translate(458.6309,155.6826)">
                                                                        <path
                                                                            d="M 0,0 C 0,-25.333 20.536,-45.87 45.869,-45.87"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1334" />
                                                                    </g>
                                                                    <g id="g1336"
                                                                        transform="translate(328.0459,9.7827)">
                                                                        <path
                                                                            d="M 0,0 C 0,25.333 -20.537,45.87 -45.87,45.87"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1338" />
                                                                    </g>
                                                                    <g id="g1340"
                                                                        transform="translate(190.7441,434.7939)">
                                                                        <path
                                                                            d="m 0,0 4.225,21.186 c 1.084,5.434 5.853,9.347 11.394,9.347 h 99.274 c 5.541,0 10.31,-3.913 11.394,-9.347 l 25.657,-128.662 c 1.625,-8.149 -4.608,-15.749 -12.918,-15.749 H -8.515 c -8.309,0 -14.543,7.6 -12.918,15.749 l 14.588,73.154"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1342" />
                                                                    </g>
                                                                    <g id="g1344"
                                                                        transform="translate(227.7646,445.7456)">
                                                                        <path
                                                                            d="m 0,0 v 28.236 c 0,15.594 12.642,28.236 28.235,28.236 15.594,0 28.236,-12.642 28.236,-28.236 L 56.471,0"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1346" />
                                                                    </g>
                                                                    <g id="g1348"
                                                                        transform="translate(96.2227,185.459)">
                                                                        <path
                                                                            d="m 0,0 154.614,93.22 c 3.176,1.915 7.151,1.915 10.326,0 L 319.555,0"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1350" />
                                                                    </g>
                                                                    <g id="g1352"
                                                                        transform="translate(42.6875,49.0469)">
                                                                        <path d="M 0,0 H 22.152"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1354" />
                                                                    </g>
                                                                    <g id="g1356"
                                                                        transform="translate(91.8662,49.0469)">
                                                                        <path d="M 0,0 H 22.153"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1358" />
                                                                    </g>
                                                                    <g id="g1360"
                                                                        transform="translate(141.0459,49.0469)">
                                                                        <path d="M 0,0 H 22.153"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1362" />
                                                                    </g>
                                                                    <g id="g1364"
                                                                        transform="translate(410.8125,106.5322)">
                                                                        <path
                                                                            d="m 0,0 c 0,0 -10.751,9.038 -23.43,5.218 -11.642,-3.507 -13.27,-16.93 -4.847,-22.422 0,0 8.264,-3.687 17.43,-7.068 22.067,-8.139 12.562,-29.487 -5.202,-29.487 -8.896,0 -16.362,3.896 -20.881,8.882"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1366" />
                                                                    </g>
                                                                    <g id="g1368"
                                                                        transform="translate(393.3379,41.8613)">
                                                                        <path d="M 0,0 V 10.942"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1370" />
                                                                    </g>
                                                                    <g id="g1372"
                                                                        transform="translate(393.3379,112.6821)">
                                                                        <path d="M 0,0 V 10.922"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1374" />
                                                                    </g>
                                                                    <g id="g1376"
                                                                        transform="translate(398.3604,9.7827)">
                                                                        <path
                                                                            d="m 0,0 h -108.365 c -4.319,0 -7.82,3.501 -7.82,7.82 v 130.26 c 0,4.319 3.501,7.82 7.82,7.82 H 98.32 c 4.319,0 7.82,-3.501 7.82,-7.82 V 7.82 C 106.14,3.501 102.639,0 98.32,0 H 35"
                                                                            style="fill:none;stroke:#000000;stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                                            id="path1378" />
                                                                    </g>
                                                                </g>
                                                            </g>
                                                        </g>
                                                    </svg> @lang('lang_v1.checkout_multi_pay') </button>

                                                <button type="button"
                                                    class="pos-form-actions_btn btn btn-success @if (!$is_mobile)  @endif btn-flat no-print @if ($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif pos-express-finalize @if ($is_mobile) col-xs-6 @endif"
                                                    data-pay_method="cash" title="@lang('tooltip.express_checkout')"> <svg
                                                        id="Layer_1" enable-background="new 0 0 497 497" height="30px"
                                                        viewBox="0 0 497 497" width="30px"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <g>
                                                            <g>
                                                                <g>
                                                                    <g>
                                                                        <g>
                                                                            <path
                                                                                d="m212.903 479.445-156.728-122.351c-7.428-5.798-8.731-16.521-2.911-23.942l256.186-326.608c5.812-7.41 16.53-8.722 23.947-2.932l156.729 122.351c7.428 5.798 8.731 16.521 2.911 23.942l-256.186 326.608c-5.813 7.41-16.53 8.722-23.948 2.932z"
                                                                                fill="#57ad7b" />
                                                                        </g>
                                                                    </g>
                                                                </g>
                                                            </g>
                                                            <g>
                                                                <g>
                                                                    <g>
                                                                        <g>
                                                                            <path
                                                                                d="m113.949 489.452-110.725-164.993c-5.25-7.823-3.152-18.423 4.684-23.67l345.134-231.069c7.827-5.24 18.417-3.155 23.661 4.659l110.725 164.993c5.25 7.823 3.152 18.423-4.684 23.67l-345.134 231.069c-7.827 5.24-18.417 3.155-23.661-4.659z"
                                                                                fill="#81cda1" />
                                                                            <path
                                                                                d="m487.428 239.372-110.724-164.993c-5.244-7.814-15.834-9.899-23.661-4.659l-13.431 8.992 107.817 160.66c5.25 7.823 3.152 18.423-4.684 23.67l-331.704 222.077 2.908 4.333c5.244 7.814 15.834 9.899 23.661 4.659l345.134-231.069c7.837-5.247 9.934-15.847 4.684-23.67z"
                                                                                fill="#60bf88" />
                                                                            <ellipse cx="245.326" cy="281.915"
                                                                                fill="#29795d" rx="90.929"
                                                                                ry="91.032"
                                                                                transform="matrix(.058 -.998 .998 .058 -50.288 510.55)" />
                                                                        </g>
                                                                        <g fill="#29795d">
                                                                            <path
                                                                                d="m55.139 269.168c22.741 33.887 13.677 79.77-20.245 102.481l-31.684-47.212c-5.243-7.813-3.14-18.4 4.698-23.647z" />
                                                                            <path
                                                                                d="m82.352 442.368c33.994-22.759 79.949-13.797 102.642 20.019l-47.362 31.709c-7.837 5.247-18.441 3.167-23.684-4.645z" />
                                                                            <path
                                                                                d="m435.536 294.695c-22.741-33.887-13.677-79.77 20.245-102.481l31.684 47.212c5.243 7.813 3.14 18.4-4.698 23.647z" />
                                                                            <path
                                                                                d="m408.323 121.495c-33.994 22.76-79.949 13.797-102.642-20.019l47.362-31.709c7.837-5.247 18.441-3.167 23.684 4.645z" />
                                                                        </g>
                                                                    </g>
                                                                    <g>
                                                                        <g>
                                                                            <path
                                                                                d="m359.549 212.948c-2.419 0-4.793-1.168-6.239-3.329-2.304-3.442-1.382-8.1 2.06-10.405l35.307-23.638c3.441-2.303 8.101-1.382 10.405 2.06s1.382 8.1-2.06 10.405l-35.307 23.638c-1.281.858-2.732 1.269-4.166 1.269z"
                                                                                fill="#29795d" />
                                                                        </g>
                                                                        <g>
                                                                            <path
                                                                                d="m95.812 389.522c-2.419 0-4.792-1.168-6.239-3.329-2.304-3.442-1.382-8.1 2.06-10.404l35.306-23.638c3.441-2.305 8.099-1.382 10.405 2.06 2.304 3.442 1.382 8.1-2.06 10.404l-35.306 23.638c-1.282.858-2.732 1.269-4.166 1.269z"
                                                                                fill="#29795d" />
                                                                        </g>
                                                                    </g>
                                                                </g>
                                                                <path
                                                                    d="m280.379 321.779c10.814-11.145 11.166-26.564 6.246-37.47-6.426-14.24-20.589-20.874-36.087-16.898-1.864.478-3.775.954-5.699 1.407l-17.015-25.354c4.274-1.388 7.605-1.026 7.934-.986 4.074.587 7.853-2.21 8.48-6.278.633-4.094-2.173-7.925-6.267-8.558-.519-.079-9.023-1.287-18.698 3.081l-3.3-4.917c-2.309-3.439-6.966-4.357-10.407-2.048-3.439 2.308-4.356 6.967-2.048 10.407l3.599 5.364c-.588.63-1.171 1.281-1.745 1.977-6.61 8.029-8.06 19.23-3.781 29.231 3.911 9.145 11.788 15.227 20.558 15.873 4.449.327 9.463.004 15.839-1.08l21.895 32.627c-4.339 2.357-7.83 3.091-14.382 3.17-4.142.05-7.459 3.448-7.408 7.59.049 4.111 3.397 7.41 7.497 7.41h.093c10.22-.124 16.122-1.946 22.583-5.678l3.697 5.509c1.447 2.157 3.818 3.322 6.234 3.322 1.437 0 2.89-.413 4.173-1.273 3.439-2.308 4.356-6.967 2.048-10.407zm-57.126-50.127c-3.108-.229-6.197-2.903-7.869-6.813-1.435-3.354-1.935-8.143.496-12.256l12.763 19.018c-1.923.141-3.74.173-5.39.051zm31.012 10.289c12.344-3.164 17.423 5.735 18.688 8.538 2.373 5.258 2.55 12.394-1.319 18.267l-17.898-26.671c.178-.046.347-.088.529-.134z"
                                                                    fill="#81cda1" />
                                                            </g>
                                                        </g>
                                                    </svg> @lang('lang_v1.express_checkout_cash')</button>

                                                @if (empty($edit))
                                                    <button type="button"
                                                        class="pos-form-actions_btn btn btn-danger btn-flat @if ($is_mobile) col-xs-6 @else btn-xs @endif"
                                                        id="pos-cancel"> <svg height="30px" viewBox="0 0 365.71733 365"
                                                            width="30px" xmlns="http://www.w3.org/2000/svg">
                                                            <g fill="#f44336">
                                                                <path
                                                                    d="m356.339844 296.347656-286.613282-286.613281c-12.5-12.5-32.765624-12.5-45.246093 0l-15.105469 15.082031c-12.5 12.503906-12.5 32.769532 0 45.25l286.613281 286.613282c12.503907 12.5 32.769531 12.5 45.25 0l15.082031-15.082032c12.523438-12.480468 12.523438-32.75.019532-45.25zm0 0" />
                                                                <path
                                                                    d="m295.988281 9.734375-286.613281 286.613281c-12.5 12.5-12.5 32.769532 0 45.25l15.082031 15.082032c12.503907 12.5 32.769531 12.5 45.25 0l286.632813-286.59375c12.503906-12.5 12.503906-32.765626 0-45.246094l-15.082032-15.082032c-12.5-12.523437-32.765624-12.523437-45.269531-.023437zm0 0" />
                                                            </g>
                                                        </svg> @lang('sale.cancel')</button>
                                                @else
                                                    <button type="button"
                                                        class="pos-form-actions_btn btn btn-danger btn-flat hide @if ($is_mobile) col-xs-6 @else btn-xs @endif"
                                                        id="pos-delete"
                                                        @if (!empty($only_payment)) disabled @endif> <i
                                                            class="fas fa-trash-alt"></i> @lang('messages.delete')</button>
                                                @endif



                                            </div>
                                        </div>
                                    </div>
                                    @if (isset($transaction))
                                        @include('sale_pos.partials.edit_discount_modal', [
                                            'sales_discount' => $transaction->discount_amount,
                                            'discount_type' => $transaction->discount_type,
                                            'rp_redeemed' => $transaction->rp_redeemed,
                                            'rp_redeemed_amount' => $transaction->rp_redeemed_amount,
                                            'max_available' => !empty($redeem_details['points'])
                                                ? $redeem_details['points']
                                                : 0,
                                        ])
                                    @else
                                        @include('sale_pos.partials.edit_discount_modal', [
                                            'sales_discount' => $business_details->default_sales_discount,
                                            'discount_type' => 'percentage',
                                            'rp_redeemed' => 0,
                                            'rp_redeemed_amount' => 0,
                                            'max_available' => 0,
                                        ])
                                    @endif

                                    @if (isset($transaction))
                                        @include('sale_pos.partials.edit_order_tax_modal', [
                                            'selected_tax' => $transaction->tax_id,
                                        ])
                                    @else
                                        @include('sale_pos.partials.edit_order_tax_modal', [
                                            'selected_tax' => $business_details->default_sales_tax,
                                        ])
                                    @endif

                                    @include('sale_pos.partials.edit_shipping_modal')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{--        @include('sale_pos.partials.pos_form_actions') --}}
        {!! Form::close() !!}
    </section>

    <!-- This will be printed -->
    <section class="invoice print_section" id="receipt_section">
    </section>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>
    @if (empty($pos_settings['hide_product_suggestion']) && isMobile())
        @include('sale_pos.partials.mobile_product_suggestions')
    @endif
    <!-- /.content -->
    <div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <!-- quick product modal -->
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

    <div class="modal fade" id="expense_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    @include('sale_pos.partials.configure_search_modal')

    @include('sale_pos.partials.recent_transactions_modal')

    @include('sale_pos.partials.weighing_scale_modal')

@stop
@section('css')
    <!-- include module css -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
@stop
@section('javascript')
    <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    @include('sale_pos.partials.keyboard_shortcuts')

    <!-- Call restaurant module if defined -->
    @if (in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules))
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <!-- include module js -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
        @endforeach
    @endif
@endsection
