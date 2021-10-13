@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Videos            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('/admin/gallery')}}"><i class="fa fa-folder-open"></i> Gallery</a></li>
            <li><a href="{{url('/admin/videos')}}"><i class="fa fa-youtube-play"></i> Videos</a></li>
            <li class="active">Add Video</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              

              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Add Video</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  @if(Session::has('error'))
                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                  @endif
                  @if(Session::has('success'))
                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                  @endif
                  <form role="form" id="Treatmentform" method="POST" action="{{url('/admin/insertVideo')}}" enctype="multipart/form-data">
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

                        <div class="form-group{{ $errors->has('path') ? ' has-error' : '' }}">
                          <label for="path">Path<span class="required-title">*</span></label>
                          <input type="text" class="form-control required" value="{{ old('path') }}" id="path" name="path" placeholder="Enter path">
                          @if ($errors->has('path'))
                            <span class="help-block">
                              <strong>{{ $errors->first('path') }}</strong>
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