@extends('layouts.dashboard')
<?php

  $models = array(); 
  if(session()->has('model_id')){
    $model_id = session()->get('model_id');
  }
  if(@$model_id){
    $models = DB::table('models')->where('id', $model_id)->get();
  }
?>
@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Images            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('/admin/gallery')}}"><i class="fa fa-folder-open"></i> Gallery</a></li>
            <li><a href="{{url('/admin/images')}}"><i class="fa fa-image"></i> Images</a></li>
            <li class="active">Add Image</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              

              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Add Image</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  @if(Session::has('error'))
                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                  @endif
                  @if(Session::has('success'))
                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                  @endif
                  <form role="form" id="Treatmentform" method="POST" action="{{url('/admin/insertImage')}}" enctype="multipart/form-data">
                      <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                      <div class="box-body">

                        <div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">
                          <label for="title">Title<span class="required-title">*</span></label>
                          <input type="text" class="form-control required" value="{{ old('title') }}" id="title" name="title" placeholder="Enter title">
                          @if ($errors->has('title'))
                            <span class="help-block">
                              <strong>{{ $errors->first('title') }}</strong>
                            </span>
                          @endif
                        </div>

                        <div class="form-group{{ $errors->has('image') ? ' has-error' : '' }}">
                          <label for="image">Image<span class="required-title">*</span></label>
                          <input type="file" class="form-control required" value="{{ old('image') }}" id="image" name="image">
                          @if ($errors->has('image'))
                            <span class="help-block">
                              <strong>{{ $errors->first('image') }}</strong>
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