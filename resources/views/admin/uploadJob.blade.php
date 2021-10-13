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
            Treatments            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('/admin/history_jobs')}}"><i class="fa fa-tasks"></i> History Jobs</a></li>
            <li class="active">Upload Job History</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              

              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Upload Job History</h3>
                  <a download="" href="{{asset('/job-history.xlsx')}}" class="floatright btn btn-warning">Download Sample</a>
                </div><!-- /.box-header -->
                <div class="box-body">
                  @if(Session::has('error'))
                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                  @endif
                  @if(Session::has('success'))
                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                  @endif
                  <form role="form" id="Treatmentform" method="POST" action="{{url('/admin/importJobsHistory')}}" enctype="multipart/form-data">
                      <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                      <div class="box-body">

                        <div class="form-group{{ $errors->has('dealer_id') ? ' has-error' : '' }}">
                          <label for="dealer_id">Dealer<span class="required-title">*</span></label>
                          <select class="form-control required" id="dealer_id" name="dealer_id">
                            <option value="">Select Dealer</option>
                            @foreach($dealers as $dealer)
                              <option value="{{$dealer->dealer_id}}">{{$dealer->dealer_name}}</option>
                            @endforeach
                          </select>
                          @if ($errors->has('dealer_id'))
                            <span class="help-block">
                              <strong>{{ $errors->first('dealer_id') }}</strong>
                            </span>
                          @endif
                        </div>

                        <div class="form-group{{ $errors->has('csv') ? ' has-error' : '' }}">
                          <label for="csv">Upload File<span class="required-title">*</span></label><br>
                          <label class="ad">(File must be type of xlsx)</label>
                          <input type="file" class="form-control required" value="{{ old('csv') }}" id="csv" name="csv">
                          @if ($errors->has('csv'))
                            <span class="help-block">
                              <strong>{{ $errors->first('csv') }}</strong>
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