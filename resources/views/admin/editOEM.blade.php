@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            OEM            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('/admin/oems')}}"><i class="fa fa-users"></i> OEM</a></li>
            <li class="active">Edit OEM</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              

              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Edit OEM</h3>
                  <a href="{{ URL::previous() }}" class="btn btn-info floatright" style="margin-right: 10px;">Back</a>
                </div><!-- /.box-header -->
                <div class="box-body">
                  @if(Session::has('error'))
                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                  @endif
                  @if(Session::has('success'))
                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                  @endif
                  <form role="form" id="Staffform" method="POST" action="{{url('/admin/updateOEM')}}" enctype="multipart/form-data">
                      <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                      <input type="hidden" name="id" value="{{$oemEdit->id}}">
                      <div class="box-body">
                        <div class="form OEM{{ $errors->has('OEM') ? ' has-error' : '' }}">
                          <label for="OEM">OEM<span class="required-title">*</span></label>
                          <input type="text" class="form-control required" value="{{ old('OEM',$oemEdit->oem) }}" id="name" name="OEM" placeholder="Enter oem">
                          @if ($errors->has('OEM'))
                            <span class="help-block">
                              <strong>{{ $errors->first('OEM') }}</strong>
                            </span>
                          @endif
                        </div>
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