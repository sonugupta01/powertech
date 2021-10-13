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
      <li><a href="{{url('/admin/designation')}}"><i class="fa fa-users"></i> Designation</a></li>
      <li class="active">Edit Designation</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Edit Designation</h3>
            <a href="{{ URL::previous() }}" class="btn btn-info floatright" style="margin-right: 10px;">Back</a>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('error'))
              <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
              <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="Staffform" method="POST" action="{{url('/admin/updateDesignation')}}" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                <input type="hidden" name="id" value="{{$designationEdit->id}}">
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('level') ? ' has-error' : '' }}" id="level">
                        <label for="level">Level<span class="required-title">*</span></label>
                        <select class="form-control required" id="level" name="level">
                          <option value="">Select Level</option>
                          @foreach($levels as $level)
                          <option @if($designationEdit->level == $level->id) {{ 'selected' }} @endif value="{{$level->id}}">{{$level->level}}</option>
                          @endforeach
                        </select>
                        @if ($errors->has('level'))
                        <span class="help-block">
                          <strong>{{ $errors->first('level') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-6">
                      {{-- <div class="form-group{{ $errors->has('department_id') ? ' has-error' : '' }}" id="level">
                        <label for="department_id">Department<span class="required-title">*</span></label>
                        <select class="form-control required" id="department_id" name="department_id">
                          <option value="">Select Level</option>
                          @foreach($departments as $department)
                          <option @if($designationEdit->department_id == $department->id) {{ 'selected' }} @endif value="{{$department->id}}">{{ucwords($department->name)}}</option>
                          @endforeach
                        </select>
                        @if ($errors->has('department_id'))
                        <span class="help-block">
                          <strong>{{ $errors->first('department_id') }}</strong>
                        </span>
                        @endif
                      </div> --}}
                      <div class="form-group{{ $errors->has('designation') ? ' has-error' : '' }}">
                        <label for="designation">Designation<span class="required-title">*</span></label>
                        <input type="text" class="form-control required" value="{{ old('designation',$designationEdit->designation) }}" id="designation" name="designation" placeholder="Enter designation">
                        @if ($errors->has('designation'))
                          <span class="help-block">
                            <strong>{{ $errors->first('designation') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                  </div>
                  <!-- <div class="row">
                    <div class="col-md-6"></div>
                    <div class="col-md-6"></div>
                  </div> -->
                  
                  
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