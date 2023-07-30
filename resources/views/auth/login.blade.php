@extends('layouts.auth3')
@section('title', __('lang_v1.login'))
@section('content')
    <div class="login-form col-md-12 col-xs-12 right-col-content">
        <div class="login-form-headding">
            <div>
                <h2>
                    @lang('lang_v1.login_heading_brand')
                </h2>
                <p>@lang('lang_v1.login_heading')</p>
            </div>
{{--           <div>--}}
{{--            <img src="{{ asset('/logo.png') }}" alt="" style="width: 200px">--}}
{{--           </div>--}}
        </div>
{{--        <p class="form-header text-white">@lang('lang_v1.login')</p>--}}
   
        <form method="POST" action="{{ route('login') }}" id="login-form">
            {{ csrf_field() }}
            <div class="form-group has-feedback {{ $errors->has('username') ? ' has-error' : '' }}">
                @php
                    $username = old('username');
                    $password = null;
                    if(config('app.env') == 'demo'){
                        $username = 'admin';
                        $password = '123456';

                        $demo_types = array(
                            'all_in_one' => 'admin',
                            'super_market' => 'admin',
                            'pharmacy' => 'admin-pharmacy',
                            'electronics' => 'admin-electronics',
                            'services' => 'admin-services',
                            'restaurant' => 'admin-restaurant',
                            'superadmin' => 'superadmin',
                            'woocommerce' => 'woocommerce_user',
                            'essentials' => 'admin-essentials',
                            'manufacturing' => 'manufacturer-demo',
                        );

                        if( !empty($_GET['demo_type']) && array_key_exists($_GET['demo_type'], $demo_types) ){
                            $username = $demo_types[$_GET['demo_type']];
                        }
                    }
                @endphp
                <label for="#">@lang('lang_v1.username')</label>

                <input id="username" type="text" class="form-control" name="username" value="{{ $username }}" required autofocus placeholder="@lang('lang_v1.username')">
                <span class="fa fa-user form-control-feedback"></span>
                @if ($errors->has('username'))
                    <span class="help-block">
                        <strong>{{ $errors->first('username') }}</strong>
                    </span>
                @endif
            </div>
            <div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
                <label for="#">@lang('lang_v1.password')</label>
                <input id="password" type="password" class="form-control" name="password"
                       value="{{ $password }}" required placeholder="@lang('lang_v1.password')">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                @if ($errors->has('password'))
                    <span class="help-block">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                @endif
            </div>
            <div class="form-group">
                <div class="checkbox icheck">
                    <label>
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> @lang('lang_v1.remember_me')
                    </label>
                    @if(config('app.env') != 'demo')
                    <a href="{{ route('password.request') }}" class="pull-right">
                        @lang('lang_v1.forgot_your_password')
                    </a>
                @endif
                </div>
             
            </div>
            <br>
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-flat login-btn">@lang('lang_v1.login')</button>
               
            </div>
            <div>
                <p class="havent">Don’t have an account? <a href="#">Sign up</a></p>
            </div>
        </form>
    </div>
@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function(){
            $('#change_lang').change( function(){
                window.location = "{{ route('login') }}?lang=" + $(this).val();
            });

            $('a.demo-login').click( function (e) {
                e.preventDefault();
                $('#username').val($(this).data('admin'));
                $('#password').val("{{$password}}");
                $('form#login-form').submit();
            });
        })
    </script>
@endsection
