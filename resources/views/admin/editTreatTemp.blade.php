@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Treatment Template            <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('admin/treatmentTemplates')}}"><i class="fa fa-dashboard"></i> Treatment Template</a></li>
      <li class="active">Edit Treatment Template</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        

        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Edit Treatment Template</h3>
            <a href="{{ URL::previous() }}" class="btn btn-info floatright" style="margin-right: 10px;">Back</a>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('error'))
              <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
              <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="Templateform" method="POST" action="{{url('/admin/updateTreatmentTemp')}}" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                <input type="hidden" name="tempId" value="{{$result->id}}">
                <div class="box-body">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="form-group{{ $errors->has('oem_id') ? ' has-error' : '' }}">
                        <label for="oem_id">OEM<span class="required-title">*</span></label>
                        <select class="form-control required" id="oem_id" name="oem_id">
                          <option value="">Select OEM</option>
                          @foreach($oems as $oem)
                          <option @if($result->oem_id==$oem->id) {{ 'selected' }} @endif value="{{ $oem->id }}">{{ ucwords($oem->oem) }}</option>
                          @endforeach
                        </select>
                        @if ($errors->has('oem_id'))
                        <span class="help-block">
                          <strong>{{ $errors->first('oem_id') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="form-group{{ $errors->has('temp_name') ? ' has-error' : '' }}">
                        <label for="temp_name">Template Name<span class="required-title">*</span></label>
                        <input type="text" class="form-control required" value="{{ old('temp_name', $result->temp_name) }}" id="temp_name" name="temp_name" placeholder="Enter Template Name">
                        @if ($errors->has('temp_name'))
                          <span class="help-block">
                            <strong>{{ $errors->first('temp_name') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                  </div>
                  <div class="form-group{{ $errors->has('temp_description') ? ' has-error' : '' }}">
                    <label for="temp_description">Template Description<span class="required-title">*</span></label>
                    <textarea type="text" name="temp_description" class="form-control required" id="temp_description" placeholder="Enter Template Description">{{ old('temp_description',$result->temp_description) }}</textarea>
                    @if ($errors->has('temp_description'))
                      <span class="help-block">
                        <strong>{{ $errors->first('temp_description') }}</strong>
                      </span>
                    @endif
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