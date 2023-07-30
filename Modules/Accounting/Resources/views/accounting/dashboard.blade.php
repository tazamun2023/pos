@extends('layouts.app')

@section('title', __('accounting::lang.accounting'))

@section('content')
    @include('accounting::layouts.nav')

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group pull-right">
                    <div class="input-group">
                        <button type="button" class="btn btn-primary" id="dashboard_date_filter">
                            <span>
                                <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }}
                            </span>
                            <i class="fa fa-caret-down"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', [
                    'class' => 'box-primary',
                    'title' => __('accounting::lang.chart_of_account_overview'),
                ])
                    <div style='display: flex !important; justify-content: space-between;padding: 20px 0;'>
                        @foreach ($account_types as $k => $v)
                            @php
                                $bal = 0;
                                foreach ($coa_overview as $overview) {
                                    if ($overview->account_primary_type == $k && !empty($overview->balance)) {
                                        $bal = (float) $overview->balance;
                                    }
                                }
                            @endphp
                            <div
                                style='width: 18% !important; height: 216px !important; background: #FFFFFF; border: 1px solid #C3C3C3; border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;'>
                                <div
                                    style="background: rgba(158, 211, 116, 0.33);
                                    border-radius: 5px;
                                    width: fit-content;
                                    overflow: hidden;
                                    height: 44px;
                                    width: 44px;
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                    border: 1px solid #9ED374;"
                                >
                                    <svg width="21" height="20" viewBox="0 0 21 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M12 9.12042e-07C13.83 -0.000872552 15.605 0.625667 17.0289 1.77513C18.4528 2.92458 19.4396 4.52748 19.8247 6.31647C20.2098 8.10546 19.9699 9.97241 19.1451 11.6059C18.3202 13.2395 16.9603 14.5408 15.292 15.293C14.7588 16.4719 13.9475 17.5039 12.9279 18.3004C11.9082 19.097 10.7106 19.6343 9.43766 19.8663C8.16473 20.0984 6.85452 20.0182 5.61939 19.6327C4.38426 19.2472 3.26106 18.5678 2.34614 17.6529C1.43121 16.738 0.751858 15.6148 0.36634 14.3796C-0.0191778 13.1445 -0.0993549 11.8343 0.132685 10.5614C0.364724 9.28844 0.902056 8.0908 1.6986 7.07114C2.49514 6.05148 3.52711 5.24024 4.70603 4.707C5.34017 3.30389 6.36561 2.1135 7.65937 1.27861C8.95313 0.443714 10.4603 -0.000237065 12 9.12042e-07V9.12042e-07ZM8.00003 6C7.21209 6 6.43188 6.1552 5.70392 6.45672C4.97597 6.75825 4.31454 7.20021 3.75738 7.75736C3.20023 8.31451 2.75828 8.97595 2.45675 9.7039C2.15522 10.4319 2.00003 11.2121 2.00003 12C2.00003 12.7879 2.15522 13.5681 2.45675 14.2961C2.75828 15.0241 3.20023 15.6855 3.75738 16.2426C4.31454 16.7998 4.97597 17.2417 5.70392 17.5433C6.43188 17.8448 7.21209 18 8.00003 18C9.59132 18 11.1174 17.3679 12.2427 16.2426C13.3679 15.1174 14 13.5913 14 12C14 10.4087 13.3679 8.88258 12.2427 7.75736C11.1174 6.63214 9.59132 6 8.00003 6ZM9.00002 7V8H11V10H7.00003C6.87508 9.99977 6.75458 10.0463 6.66224 10.1305C6.56991 10.2147 6.51244 10.3304 6.50115 10.4548C6.48986 10.5793 6.52557 10.7034 6.60125 10.8028C6.67692 10.9023 6.78708 10.9697 6.91003 10.992L7.00003 11H9.00002C9.66307 11 10.299 11.2634 10.7678 11.7322C11.2366 12.2011 11.5 12.837 11.5 13.5C11.5 14.163 11.2366 14.7989 10.7678 15.2678C10.299 15.7366 9.66307 16 9.00002 16V17H7.00003V16H5.00003V14H9.00002C9.12497 14.0002 9.24547 13.9537 9.33781 13.8695C9.43014 13.7853 9.48761 13.6696 9.4989 13.5452C9.51019 13.4207 9.47448 13.2966 9.3988 13.1972C9.32313 13.0977 9.21297 13.0303 9.09002 13.008L9.00002 13H7.00003C6.33698 13 5.7011 12.7366 5.23226 12.2678C4.76342 11.7989 4.50003 11.163 4.50003 10.5C4.50003 9.83696 4.76342 9.20107 5.23226 8.73223C5.7011 8.26339 6.33698 8 7.00003 8V7H9.00002ZM12 2C11.1527 1.99901 10.3148 2.17794 9.54185 2.52496C8.76885 2.87198 8.07835 3.37918 7.51603 4.013C8.64629 3.94439 9.77824 4.1165 10.837 4.51795C11.8958 4.9194 12.8573 5.54105 13.658 6.34178C14.4586 7.14252 15.0801 8.1041 15.4815 9.16293C15.8828 10.2218 16.0548 11.3537 15.986 12.484C16.8952 11.6756 17.5372 10.6099 17.8268 9.42831C18.1165 8.24668 18.04 7.00491 17.6077 5.86772C17.1753 4.73053 16.4075 3.75164 15.406 3.06088C14.4045 2.37013 13.2166 2.00014 12 2V2Z"
                                            fill="#05A44E" />
                                    </svg>
                                </div>
                                <div>
                                    <span style="display: block;font-size: 20px;color: #A0A0A0;">{{ $v['label'] }}</span>
                                    {{-- Suffix CR/DR as per value --}}
                                    <span style="display: block;font-size: 20px;color: #2E2D4D;font-weight: 600">
                                        @if ($bal < 0)
                                            {{ in_array($v['label'], ['Asset', 'Expenses']) ? ' (CR)' : ' (DR)' }}
                                        @endif
                                    </span>
                                    <span style="display: block;font-size: 24px;color: #2E2D4D;">@format_currency(abs($bal))</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endcomponent
            </div>
{{--            <div class="col-md-12">--}}
{{--                @component('components.widget', [--}}
{{--                    'class' => 'box-primary',--}}
{{--                    'title' => __('accounting::lang.chart_of_account_overview'),--}}
{{--                ])--}}
{{--                    <div class="col-md-4">--}}
{{--                        <table class="table table-bordered table-striped">--}}
{{--                            <thead>--}}
{{--                                <tr>--}}
{{--                                    <th>@lang('accounting::lang.account_type')</th>--}}
{{--                                    <th>@lang('accounting::lang.current_balance')</th>--}}
{{--                                </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                                @foreach ($account_types as $k => $v)--}}
{{--                                    @php--}}
{{--                                        $bal = 0;--}}
{{--                                        foreach ($coa_overview as $overview) {--}}
{{--                                            if ($overview->account_primary_type == $k && !empty($overview->balance)) {--}}
{{--                                                $bal = (float) $overview->balance;--}}
{{--                                            }--}}
{{--                                        }--}}
{{--                                    @endphp--}}

{{--                                    <tr>--}}
{{--                                        <td>--}}
{{--                                            {{ $v['label'] }}--}}

{{--                                            --}}{{-- Suffix CR/DR as per value --}}
{{--                                            @if ($bal < 0)--}}
{{--                                                {{ in_array($v['label'], ['Asset', 'Expenses']) ? ' (CR)' : ' (DR)' }}--}}
{{--                                            @endif--}}
{{--                                        </td>--}}
{{--                                        <td>--}}
{{--                                            @format_currency(abs($bal))--}}
{{--                                        </td>--}}
{{--                                    </tr>--}}
{{--                                @endforeach--}}
{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-8">--}}
{{--                        {!! $coa_overview_chart->container() !!}--}}
{{--                    </div>--}}
{{--                @endcomponent--}}
{{--            </div>--}}
        </div>

        <div class="row">
            @foreach ($all_charts as $key => $chart)
                <div class="col-md-6">
                    @component('components.widget', ['class' => 'box-primary', 'title' => __('accounting::lang.' . $key)])
                        {!! $chart->container() !!}
                    @endcomponent
                </div>
            @endforeach
        </div>
    </section>
@stop

@section('javascript')
    {!! $coa_overview_chart->script() !!}
    @foreach ($all_charts as $key => $chart)
        {!! $chart->script() !!}

        <script type="text/javascript">
            $(document).ready(function() {
                dateRangeSettings.startDate = moment('{{ $start_date }}', 'YYYY-MM-DD');
                dateRangeSettings.endDate = moment('{{ $end_date }}', 'YYYY-MM-DD');
                $('#dashboard_date_filter').daterangepicker(dateRangeSettings, function(start, end) {
                    $('#dashboard_date_filter span').html(
                        start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
                    );

                    var start = $('#dashboard_date_filter')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD');

                    var end = $('#dashboard_date_filter')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD');
                    var url =
                        "{{ action('\Modules\Accounting\Http\Controllers\AccountingController@dashboard') }}?start_date=" +
                        start + '&end_date=' + end;

                    window.location.href = url;
                });
            });
        </script>
    @endforeach


@stop
