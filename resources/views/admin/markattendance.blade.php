@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Mark Attendance            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('admin/attendance')}}"><i class="fa fa-users"></i>Attendance</a></li>
            <li class="active">Mark Attendance</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              

              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Mark Attendance</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  @if(Session::has('error'))
                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                  @endif
                  @if(Session::has('success'))
                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                  @endif
                  <form role="form" id="Staffform" method="POST" action="{{url('/admin/markattendance')}}" enctype="multipart/form-data">
                      <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                      <div class="box-body">
                <div class="col-xs-6 col-md-6">
                  <label>Dealer</label>
                 
                  <select class="form-control" name="dealer" id="dealer_id">
                    <option value="">Select Dealer</option>
                    @foreach(@$dealers as $value)
                    <div class="form-group report-field col-md-3 ol-sm-3 ol-xs-12">

                      <option {{ @$value->id == @$_GET['dealer_id_hidden'] ? 'selected' : '' }} value="{{ $value->id }}">{{ $value->name }}
                      </option>
                      @endforeach
                  </select>

                  <br>
                
                  <label>Employees</label>
                  <select class="form-control" name="employee">
                    <option value="">Select Employee</option>
                    @foreach(@$employees as $employee)
                    <option {{ @$emp == $employee->id ? 'selected' : '' }} value="{{ $employee->id }}">
                      {{ $employee->name }}</option>
                    @endforeach
                  </select> 
                  
                  <br>OR <br>

                  <label>Employee Codes</label>
                  <select class="form-control" name="employeecode">
                    <option value="">Select Code</option>
                    @foreach(@$empcodes as $code)
                    <option {{ @$empcode == $code->user_id ? 'selected' : '' }} value="{{ $code->user_id }}">
                      {{ $code->emp_code }}</option>
                    @endforeach
                  </select>

                   <br>
                  <label>Date</label>
                  <input type="date" id="start_date" name="start_date" placeholder="Start Date"
                    value="" class="form-control" autocomplete="off" />
                  
                   <br> <label>In Time</label>
                  <input type="time" id="in_time" name="in_time" placeholder="In Time"
                    value="" class="form-control" autocomplete="off" />

                   <br> <label>Out Time</label>
                  <input type="time" id="out_time" name="out_time" placeholder="Out Time"
                    value="" class="form-control" autocomplete="off" />

                      <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                      </div>
                </div>
                  
                    </form>
                  <form action="{{url('admin/mark_attendance')}}" method="GET" id="get_employees">
                    <input type="hidden" value="" id="dealer_id_hidden" name="dealer_id_hidden">
                  </form>
                    <script>
                      $("#dealer_id").change(function () {
                        
                        var dealer_id = $('#dealer_id').val();
                        $('#dealer_id_hidden').val(dealer_id);
                        $('#get_employees').submit();
                      });
                    </script>

                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div><!-- /.col -->
          </div><!-- /.row -->
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->    
@endsection