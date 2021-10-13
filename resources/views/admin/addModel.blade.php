@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Models            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('/admin/models/')}}"><i class="fa fa-users"></i> Models</a></li>
            <li class="active">Add Model</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Add Model</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  @if(Session::has('error'))
                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                  @endif
                  @if(Session::has('success'))
                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                  @endif
                  <form role="form" id="Staffform" method="POST" action="{{url('/admin/insertModel')}}" enctype="multipart/form-data">
                      <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                      <div class="box-body">
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                              <label for="name">Name<span class="required-title">*</span></label>
                              <input type="text" class="form-control required" value="{{ old('name') }}" id="name" name="name" placeholder="Enter name">
                              @if ($errors->has('name'))
                                <span class="help-block">
                                  <strong>{{ $errors->first('name') }}</strong>
                                </span>
                              @endif
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group{{ $errors->has('size') ? ' has-error' : '' }}">
                              <label for="size">Size<span class="required-title">*</span></label>
                              <select class="form-control required" id="size" name="size">
                                <option value="">Select Size</option>
                                <option value="1">Large</option>
                                <option value="2">Medium</option>
                                <option value="3">Small</option>
                              </select>
                              @if ($errors->has('size')) 
                                <span class="help-block">
                                  <strong>{{ $errors->first('size') }}</strong>
                                </span>
                              @endif
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group{{ $errors->has('oem_id') ? ' has-error' : '' }}">
                              <label for="oem_id">OEM<span class="required-title">*</span></label>
                              <select class="form-control required" id="oem_id" name="oem_id">
                                <option value="">Select OEM</option>
                                @foreach($oemlist as $key => $oem)
                                <option value="{{$oem->id}}">{{ucwords($oem->oem)}}</option>
                                @endforeach
                              </select>
                              @if ($errors->has('oem_id')) 
                                <span class="help-block">
                                  <strong>{{ $errors->first('oem_id') }}</strong>
                                </span>
                              @endif
                            </div>
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