@inject('request', 'Illuminate\Http\Request')
<!-- Main Header -->
<header class="main-header no-print">
    <div>
        <a href="{{ route('home') }}" class="logo" style="background: #000080!important">

            <span class="logo-lg">{{ Session::get('business.name') }} <i class="fa fa-circle text-success"
                    id="online_indicator"></i></span>

        </a>

    </div>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top f_navbar" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" style="color: rgb(59, 53, 53); font-size: 20px" data-toggle="offcanvas"
            role="button">
            &#9776;
            <span class="sr-only">Toggle navigation</span>
        </a>

        {{--      @if (Module::has('Superadmin')) --}}
        {{--        @includeIf('superadmin::layouts.partials.active_subscription') --}}
        {{--      @endif --}}

        @if (!empty(session('previous_user_id')) && !empty(session('previous_username')))
            <a href="{{ route('sign-in-as-user', session('previous_user_id')) }}"
                class="btn btn-flat btn-danger m-8 btn-sm mt-10"><i class="fas fa-undo"></i> @lang('lang_v1.back_to_username', ['username' => session('previous_username')])</a>
        @endif
        <div class="f_navbar-ch1">
            <div class="f_search">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_83_2417)">
                        <path
                            d="M8.25 1.5C11.976 1.5 15 4.524 15 8.25C15 11.976 11.976 15 8.25 15C4.524 15 1.5 11.976 1.5 8.25C1.5 4.524 4.524 1.5 8.25 1.5ZM8.25 13.5C11.1503 13.5 13.5 11.1503 13.5 8.25C13.5 5.349 11.1503 3 8.25 3C5.349 3 3 5.349 3 8.25C3 11.1503 5.349 13.5 8.25 13.5ZM14.6138 13.5533L16.7355 15.6743L15.6743 16.7355L13.5533 14.6138L14.6138 13.5533Z"
                            fill="#A0A0A0" />
                    </g>
                    <defs>
                        <clipPath id="clip0_83_2417">
                            <rect width="18" height="18" fill="white" />
                        </clipPath>
                    </defs>
                </svg>
                <input type="text" name="" id="" placeholder="Search for tasks">
            </div>
        </div>
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu f_navbar-ch2">
            <div class="f_content-btn_group">



                @if (Module::has('Essentials'))
                    @includeIf('essentials::layouts.partials.header_part')
                @endif
                @if (in_array('pos_sale', $enabled_modules))
                    @can('direct_sell.access')
                        <a href='{{ action([\App\Http\Controllers\SellController::class, 'create']) }}'>
                            <div class="f_content-btn-1">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_83_2437)">
                                        <path
                                            d="M7 8V6C7 4.67392 7.52678 3.40215 8.46447 2.46447C9.40215 1.52678 10.6739 1 12 1C13.3261 1 14.5979 1.52678 15.5355 2.46447C16.4732 3.40215 17 4.67392 17 6V8H20C20.2652 8 20.5196 8.10536 20.7071 8.29289C20.8946 8.48043 21 8.73478 21 9V21C21 21.2652 20.8946 21.5196 20.7071 21.7071C20.5196 21.8946 20.2652 22 20 22H4C3.73478 22 3.48043 21.8946 3.29289 21.7071C3.10536 21.5196 3 21.2652 3 21V9C3 8.73478 3.10536 8.48043 3.29289 8.29289C3.48043 8.10536 3.73478 8 4 8H7ZM7 10H5V20H19V10H17V12H15V10H9V12H7V10ZM9 8H15V6C15 5.20435 14.6839 4.44129 14.1213 3.87868C13.5587 3.31607 12.7956 3 12 3C11.2044 3 10.4413 3.31607 9.87868 3.87868C9.31607 4.44129 9 5.20435 9 6V8Z"
                                            fill="#49487A" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_83_2437">
                                            <rect width="24" height="24" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                                <span>Add sale</span>
                            </div>
                        </a>
                    @endcan
                @endif
                @if (config('app.env') != 'demo')
                    <div class="f_content-btn-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_83_2444)">
                                <path
                                    d="M17 3H21C21.2652 3 21.5196 3.10536 21.7071 3.29289C21.8946 3.48043 22 3.73478 22 4V20C22 20.2652 21.8946 20.5196 21.7071 20.7071C21.5196 20.8946 21.2652 21 21 21H3C2.73478 21 2.48043 20.8946 2.29289 20.7071C2.10536 20.5196 2 20.2652 2 20V4C2 3.73478 2.10536 3.48043 2.29289 3.29289C2.48043 3.10536 2.73478 3 3 3H7V1H9V3H15V1H17V3ZM20 11H4V19H20V11ZM15 5H9V7H7V5H4V9H20V5H17V7H15V5ZM6 13H8V15H6V13ZM11 13H13V15H11V13ZM16 13H18V15H16V13Z"
                                    fill="#05A44E" />
                            </g>
                            <defs>
                                <clipPath id="clip0_83_2444">
                                    <rect width="24" height="24" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                        <span>@lang('lang_v1.calendar')</span>

                    </div>
                @endif
                <div class=''>
                    <button id="btnCalculator" title="@lang('lang_v1.calculator')" type="button"
                        class="popover-default hidden-xs f_content-btn-3" data-toggle="popover" data-trigger="click"
                        data-content='@include('layouts.partials.calculator')' data-html="true" data-placement="bottom">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_83_2451)">
                                <path
                                    d="M4 2H20C20.2652 2 20.5196 2.10536 20.7071 2.29289C20.8946 2.48043 21 2.73478 21 3V21C21 21.2652 20.8946 21.5196 20.7071 21.7071C20.5196 21.8946 20.2652 22 20 22H4C3.73478 22 3.48043 21.8946 3.29289 21.7071C3.10536 21.5196 3 21.2652 3 21V3C3 2.73478 3.10536 2.48043 3.29289 2.29289C3.48043 2.10536 3.73478 2 4 2ZM5 4V20H19V4H5ZM7 6H17V10H7V6ZM7 12H9V14H7V12ZM7 16H9V18H7V16ZM11 12H13V14H11V12ZM11 16H13V18H11V16ZM15 12H17V18H15V12Z"
                                    fill="#FF8552" />
                            </g>
                            <defs>
                                <clipPath id="clip0_83_2451">
                                    <rect width="24" height="24" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                        <span>@lang('lang_v1.calculator')</span>
                    </button>
                </div>
                {{--            <div class="f_content-btn-3" id="btnCalculator" data-toggle="popover" data-trigger="click" data-content='@include("layouts.partials.calculator")' data-html="true" data-placement="div"> --}}
                {{--                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> --}}
                {{--                    <g clip-path="url(#clip0_83_2451)"> --}}
                {{--                        <path --}}
                {{--                            d="M4 2H20C20.2652 2 20.5196 2.10536 20.7071 2.29289C20.8946 2.48043 21 2.73478 21 3V21C21 21.2652 20.8946 21.5196 20.7071 21.7071C20.5196 21.8946 20.2652 22 20 22H4C3.73478 22 3.48043 21.8946 3.29289 21.7071C3.10536 21.5196 3 21.2652 3 21V3C3 2.73478 3.10536 2.48043 3.29289 2.29289C3.48043 2.10536 3.73478 2 4 2ZM5 4V20H19V4H5ZM7 6H17V10H7V6ZM7 12H9V14H7V12ZM7 16H9V18H7V16ZM11 12H13V14H11V12ZM11 16H13V18H11V16ZM15 12H17V18H15V12Z" --}}
                {{--                            fill="#FF8552" /> --}}
                {{--                    </g> --}}
                {{--                    <defs> --}}
                {{--                        <clipPath id="clip0_83_2451"> --}}
                {{--                            <rect width="24" height="24" fill="white" /> --}}
                {{--                        </clipPath> --}}
                {{--                    </defs> --}}
                {{--                </svg> --}}
                {{--                <span>Calculator</span> --}}
                {{--            </div> --}}

                @if (in_array('pos_sale', $enabled_modules))
                    @can('sell.create')
                        <a href='{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}'>
                            <div class="f_content-btn-4">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_83_2458)">
                                        <path
                                            d="M3 3H21C21.2652 3 21.5196 3.10536 21.7071 3.29289C21.8946 3.48043 22 3.73478 22 4V20C22 20.2652 21.8946 20.5196 21.7071 20.7071C21.5196 20.8946 21.2652 21 21 21H3C2.73478 21 2.48043 20.8946 2.29289 20.7071C2.10536 20.5196 2 20.2652 2 20V4C2 3.73478 2.10536 3.48043 2.29289 3.29289C2.48043 3.10536 2.73478 3 3 3ZM20 11H4V19H20V11ZM20 9V5H4V9H20ZM14 15H18V17H14V15Z"
                                            fill="#0038FF" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_83_2458">
                                            <rect width="24" height="24" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                                <span>@lang('sale.pos_sale')</span>
                            </div>
                        </a>
                    @endcan
                @endif

                {{--                @if (Module::has('Repair')) --}}
                {{--                    @includeIf('repair::layouts.partials.header') --}}
                {{--                @endif --}}
                @can('profit_loss_report.view')
                    <button type="button" id="view_todays_profit" title="{{ __('home.todays_profit') }}"
                        data-toggle="tooltip" data-placement="bottom"
                        class="btn btn-success btn-flat pull-left m-8 btn-sm mt-10 f_clock">
                        <strong><i class="fas fa-money-bill-alt fa-lg"></i></strong>
                    </button>
                @endcan

            </div>
            {{-- @if (Module::has('Essentials'))
              @includeIf('essentials::layouts.partials.header_part')
            @endif

            <div class="btn-group">
              <button id="header_shortcut_dropdown" type="button" class="btn btn-success dropdown-toggle btn-flat pull-left m-8 btn-sm mt-10" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-plus-circle fa-lg"></i>
              </button>
              <ul class="dropdown-menu">
                @if (config('app.env') != 'demo')
                  <li><a href="{{route('calendar')}}">
                      <i class="fas fa-calendar-alt" aria-hidden="true"></i> @lang('lang_v1.calendar')
                  </a></li>
                @endif
                @if (Module::has('Essentials'))
                  <li><a href="#" class="btn-modal" data-href="{{action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'create'])}}" data-container="#task_modal">
                      <i class="fas fa-clipboard-check" aria-hidden="true"></i> @lang( 'essentials::lang.add_to_do' )
                  </a></li>
                @endif
                <!-- Help Button -->
                @if (auth()->user()->hasRole('Admin#' . auth()->user()->business_id))
                  <li><a id="start_tour" href="#">
                      <i class="fas fa-question-circle" aria-hidden="true"></i> @lang('lang_v1.application_tour')
                  </a></li>
                @endif
              </ul>
            </div>
            <button id="btnCalculator" title="@lang('lang_v1.calculator')" type="button" class="btn btn-success btn-flat pull-left m-8 btn-sm mt-10 popover-default hidden-xs" data-toggle="popover" data-trigger="click" data-content='@include("layouts.partials.calculator")' data-html="true" data-placement="bottom">
                <strong><i class="fa fa-calculator fa-lg" aria-hidden="true"></i></strong>
            </button>

            @if ($request->segment(1) == 'pos')
              @can('view_cash_register')
              <button type="button" id="register_details" title="{{ __('cash_register.register_details') }}" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat pull-left m-8 btn-sm mt-10 btn-modal" data-container=".register_details_modal"
              data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getRegisterDetails'])}}">
                <strong><i class="fa fa-briefcase fa-lg" aria-hidden="true"></i></strong>
              </button>
              @endcan
              @can('close_cash_register')
              <button type="button" id="close_register" title="{{ __('cash_register.close_register') }}" data-toggle="tooltip" data-placement="bottom" class="btn btn-danger btn-flat pull-left m-8 btn-sm mt-10 btn-modal" data-container=".close_register_modal"
              data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getCloseRegister'])}}">
                <strong><i class="fa fa-window-close fa-lg"></i></strong>
              </button>
              @endcan
            @endif

            @if (in_array('pos_sale', $enabled_modules))
              @can('sell.create')
                <a href="{{action([\App\Http\Controllers\SellPosController::class, 'create'])}}" title="@lang('sale.pos_sale')" data-toggle="tooltip" data-placement="bottom" class="btn btn-flat pull-left m-8 btn-sm mt-10 btn-success">
                  <strong><i class="fa fa-th-large"></i> &nbsp; @lang('sale.pos_sale')</strong>
                </a>
              @endcan
            @endif

            @if (Module::has('Repair'))
              @includeIf('repair::layouts.partials.header')
            @endif

            @can('profit_loss_report.view')
              <button type="button" id="view_todays_profit" title="{{ __('home.todays_profit') }}" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat pull-left m-8 btn-sm mt-10">
                <strong><i class="fas fa-money-bill-alt fa-lg"></i></strong>
              </button>
            @endcan

            <div class="m-8 pull-left mt-15 hidden-xs" style="color: #fff;"><strong>{{ @format_date('now') }}</strong></div> --}}

            <ul class="nav navbar-nav f_navbar-nav">
                @include('layouts.partials.header-notifications')
                <!-- User Account Menu -->
                <li class="dropdown user user-menu f_user-menu ">
                    <!-- Menu Toggle Button -->
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <!-- The user image in the navbar-->
                        @php
                            $profile_photo = auth()->user()->media;
                        @endphp
                        @if (!empty($profile_photo))
                            <img src="{{ $profile_photo->display_url }}" class="user-image f_user-image"
                                alt="User Image">
                        @endif
                        @if (empty($profile_photo))
                            {{-- <img src="{{$profile_photo->display_url}}" class="user-image f_user-image" alt="User Image"> --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="22px" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="#6E6893">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        @endif
                        <!-- hidden-xs hides the username on small devices so only the image appears. -->
                        {{-- <span>{{ Auth::User()->first_name }} {{ Auth::User()->last_name }}</span> --}}
                    </a>
                    <ul class="dropdown-menu">
                        <!-- The user image in the menu -->
                        <li class="user-header">
                            @if (!empty(Session::get('business.logo')))
                                <img src="{{ asset('uploads/business_logos/' . Session::get('business.logo')) }}"
                                    alt="Logo">
                            @endif
                            <p>
                                {{ Auth::User()->first_name }} {{ Auth::User()->last_name }}
                            </p>
                        </li>
                        <!-- Menu Body -->
                        <!-- Menu Footer-->

                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="{{ action([\App\Http\Controllers\UserController::class, 'getProfile']) }}"
                                    class="btn btn-default btn-flat">@lang('lang_v1.profile')</a>
                            </div>
                            <div class="pull-right">
                                <a href="{{ action([\App\Http\Controllers\Auth\LoginController::class, 'logout']) }}"
                                    class="btn btn-default btn-flat">@lang('lang_v1.sign_out')</a>
                            </div>
                        </li>
                    </ul>
                </li>
                <!-- Control Sidebar Toggle Button -->
            </ul>
        </div>
    </nav>
</header>