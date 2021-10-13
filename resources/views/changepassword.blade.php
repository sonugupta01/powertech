@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Change Password            <!-- <small>advanced tables</small> -->
      </h1>
      <ol class="breadcrumb">
        <li>
          @if(Auth::user()->role==1)
            <a href="{{url('/admin/dashboard')}}">
          @else
            <a href="{{url('/')}}">
          @endif
          <i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Change Password</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Change Password</h3>
            </div><!-- /.box-header -->
            <div class="box-body">
              @if(Session::has('error'))
                <div class="alert alert-danger">{{ Session::get('error') }}</div>
              @endif
              @if(Session::has('success'))
                <div class="alert alert-success">{{ Session::get('success') }}</div>
              @endif
              <form role="form" id="planeform" method="POST" action="{{url('/updatePassword')}}" enctype="multipart/form-data">
                  <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                  <div class="box-body">
                    <div class="form-group{{ $errors->has('current_password') ? ' has-error' : '' }}">
                      <label for="current_password">Current Password<span class="required-title">*</span></label>
                      <input type="password" class="form-control required" value="{{ old('current_password') }}" id="current_password" name="current_password" placeholder="Enter current password">
                      @if ($errors->has('current_password'))
                        <span class="help-block">
                          <strong>{{ $errors->first('current_password') }}</strong>
                        </span>
                      @endif
                    </div>

                    <div class="form-group{{ $errors->has('new_password') ? ' has-error' : '' }}">
                      <label for="new_password">New Password<span class="required-title">*</span></label>
                      <input type="password" class="form-control required" value="{{ old('new_password') }}" id="new_password" name="new_password" placeholder="Enter new password">
                      @if ($errors->has('new_password'))
                        <span class="help-block">
                          <strong>{{ $errors->first('new_password') }}</strong>
                        </span>
                      @endif
                    </div>

                    <div class="form-group{{ $errors->has('confirm_password') ? ' has-error' : '' }}">
                      <label for="confirm_password">Confirm Password<span class="required-title">*</span></label>
                      <input type="password" class="form-control required" value="{{ old('confirm_password') }}" id="confirm_password" name="confirm_password" placeholder="Enter confirm password">
                      @if ($errors->has('confirm_password'))
                        <span class="help-block">
                          <strong>{{ $errors->first('confirm_password') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>
                  <!-- /.box-body -->
                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </div>
                </form>
            </div><!-- /.box-body -->
          </div><!-- /.box -->
        </div><!-- /.col -->
      </div><!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->    
@endsection