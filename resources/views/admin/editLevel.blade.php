@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Designation            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('/admin/level')}}"><i class="fa fa-users"></i> Level</a></li>
            <li class="active">Edit Level</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              

              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Edit Level</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  @if(Session::has('error'))
                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                  @endif
                  @if(Session::has('success'))
                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                  @endif
                  <form role="form" id="Levelform" method="POST" action="{{url('/admin/updateLevel')}}" enctype="multipart/form-data">
                      <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                      <input type="hidden" name="id" value="{{$levelEdit->id}}">
                      <div class="box-body">
                        <div class="form-group{{ $errors->has('level') ? ' has-error' : '' }}">
                          <label for="level">Level<span class="required-title">*</span></label>
                          <input type="text" class="form-control required" value="{{ old('level',$levelEdit->level) }}" id="level" name="level" placeholder="Enter Level">
                          @if ($errors->has('level'))
                            <span class="help-block">
                              <strong>{{ $errors->first('level') }}</strong>
                            </span>
                          @endif
                        </div>
                      <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                      </div>
                    </form>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div><!-- /.col -->
          </div><!-- /.row -->
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->    
@endsection