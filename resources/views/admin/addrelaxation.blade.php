@extends('layouts.dashboard')
@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Attendance Relaxation 
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{ url('/admin') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Attendance Relaxation</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Attendance Relaxation </h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('error'))
              <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
              <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="addrelaxation" method="POST" action="{{url('/admin/addrelaxation')}}">
                <input type="hidden" name="_token" value="<?= csrf_token(); ?>">

                <div class="box-body">
                  
                    <div class="form-group report-field col-md-3 col-sm-3 col-xs-12">
                        <label>Relaxation Time (in minutes)</label>
                        <select class="form-control" name="relax_time" >
                          <option value="" >Select Relaxation Time</option>
                          @for ($i = 0; $i <= 30; $i+=5)
                          <option value="{{$i}}" {{old('relax_time') == $i ? "selected":""}}>{{$i}}</option>
                          @endfor

                        </select>
                        @if ($errors->has('relax_time'))
                        <span class="help-block">
                          <strong>{{ $errors->first('relax_time') }}</strong>
                        </span>
                      @endif
                      </div>
                  

                      <div class="form-group report-field col-md-1 col-sm-1 col-xs-12" style="margin-top: 24px;">
                        <input class="btn btn-primary btn-div" type="submit" value="Submit">
                          
                      </div>


              </form>
          </div><!-- /.box-body -->
        </div><!-- /.box -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->


@endsection