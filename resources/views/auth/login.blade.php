@extends('layouts.app')

@section('content')

<div class="login-box">
    <div class="login-logo">
        <img src="{{asset('powertech.jpeg')}}" style="width: auto; height: 150px;">
        <!-- <a href="javascript:void(0);">Auto Solution</a> -->
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">Sign in to start your session</p>
          @if(Session::has('error'))
            <div class="alert alert-danger">{{ Session::get('error') }}</div>
          @endif
          @if(Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
          @endif
        <form method="POST" action="{{ url('checklogin') }}" aria-label="{{ __('Login') }}">
            {{ csrf_field() }}
            <div class="form-group has-feedback">
                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                    <input type="email" class="form-control" placeholder="Email" name="email" value="{{ old('email') }}">
                    @if ($errors->has('email'))
                        <span class="help-block">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>    
            </div>
            <div class="form-group has-feedback">
                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <input type="password" class="form-control" placeholder="Password" name="password" required>
                    @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>    
            </div>
            <div class="row">
                <div class="col-xs-8">
                   <!--  <div class="checkbox icheck">
                        <label style="padding-left: 20px;">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
                        </label>
                    </div> -->
                    <a href="{{ route('password.request') }}">I forgot my password</a><br>
                </div>
                <!-- /.col -->
                <div class="col-xs-4">
                <button type="submit" class="btn btn-success btn-block btn-flat">Sign In</button>
                </div>
                <!-- /.col -->
            </div>
        </form>

        
       <!--  <a href="register.html" class="text-center">Register a new membership</a> -->

    </div>
<!-- /.login-box-body -->
</div>
<!-- /.login-box -->

@endsection
