@extends('layouts.dashboard')
@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Late Attendance 
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{ url('/admin') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Late Attendance</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Late Attendance</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('error'))
              <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
              <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <div class="row">
              <div class="col-xs-12 col-sm-12 col-md-12">
                <form id="form1" action="" method="GET">

                  <div class="col-xs-12 col-sm-6 col-md-2">
                    <div class="input-group form-group">
                      <select class="form-control" id="firm_id" name="firm_id">
                        <option value="">Select Firm</option>
                        @foreach($firms as $firm)
                          <option value="{{$firm->id}}" {{(request()->firm_id==$firm->id)?'selected':''}}>{{$firm->short_code}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>

                  <div class="col-xs-12 col-sm-6 col-md-2">
                    <div class="input-group form-group">
                      <select class="form-control" id="asm_id" name="asm_id">
                        <option style="width: 350px;" value="">Select ASM</option>
                        @foreach($asms as $asm)
                          <option value="{{$asm->id}}" {{(request()->asm_id==$asm->id)?'selected':''}}>{{$asm->name}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>

                  <div class="col-xs-12 col-sm-6 col-md-2">
                    <div class="input-group form-group">
                      <select class="form-control" id="dealer_id" name="dealer_id">
                        <option style="width: 350px;" value="">Select Dealer</option>
                        @foreach($dealers as $dealer)
                          <option value="{{$dealer->id}}" {{(request()->dealer_id==$dealer->id)?'selected':''}}>{{$dealer->name}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  {{-- <div class="col-xs-12 col-sm-6 col-md-2">
                    <div class="input-group form-group">
                      <select class="form-control" id="dealer_id" name="dealer_id">
                        <option style="width: 350px;" value="">Select Dealer</option>
                        @foreach($dealers as $dealer)
                          <option value="{{$dealer->id}}" {{(request()->dealer_id==$dealer->id)?'selected':''}}>{{$dealer->name}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div> --}}

                  <div class="form-group report-field col-md-3 ol-sm-3 ol-xs-12">
                    {{-- <label>Employees</label> --}}
                    <select class="form-control" name="interval" id="interval">
                     {{-- <option value="">Select Realaxation Interval</option> --}}
                     <option value="1" {{request()->interval == 1 ? "selected":""}}>Within Relaxation</option>
                     {{-- <option value="2" {{request()->interval == 2 ? "selected":""}}>With In 1.5 Hour</option>
                     <option value="3" {{request()->interval == 3 ? "selected":""}}>After  1.5 Hour</option> --}}
                     <option value="2" {{request()->interval == 2 ? "selected":""}}>After Relaxation</option>
                    
                    </select>
                  </div>
            

                  {{-- <a href=""></a> --}}
               
                  {{-- <div class="col-xs-12 col-sm-6 col-md-2">
                    <div class="input-group from-group pull-right">
                      <select class="form-control" id="status" name="status">
                        <option value="">Status</option>
                        <option value="activated" {{$status=='activated'?'selected':''}}>Activated</option>
                        <option value="deactivated" {{$status=='deactivated'? 'selected':''}}>Deactivated</option>
                      </select>
                    </div>
                  </div> --}}
                </form>
                <div class="col-xs-12 col-sm-6 col-md-1">
                  <form method="GET" action="{{asset('admin/downloadlate_attendence')}}">
                    {{-- @csrf --}}
                    <input type="hidden" name="firm_id" value="{{@request()->firm_id}}">
                    <input type="hidden" name="asm_id" value="{{@request()->asm_id}}">
                    <input type="hidden" name="dealer_id" value="{{@request()->dealer_id}}">
                    <input type="hidden" name="interval" value="{{@request()->interval}}">
                    <input type="submit" class="btn btn-success floatright btn-div" name="download" id="download" value="Download" style="
                    margin-right: -159px;
                ">
                  </form>
                </div>
              </div>
            </div>
{{-- <form action="" method="get">

    <div class="row">
      
          <div class="form-group report-field col-md-1 col-sm-1 ol-xs-12" style="margin-top: 24px;">
            <input class="btn btn-primary btn-div" type="submit" value="Submit">
              
          </div>
    </div>
</form> --}}


            <div class="table-resposive">
                <table class="table table-bordered table-striped report-table">
                  <thead>
                    {{-- <tr>
                      <th colspan="8" style="font-size: 12px;">Total Records: {{ count(@$result) }}
                      </th>
                      <th><button id="download" class="btn btn-success"
                          style="margin: 0 auto; display: block;">Download</button></th>
                      <!-- <th><button id="downloadHVT" class="btn btn-success" style="margin: 0 auto; display: block;">Download Dealer Wise</button></th> -->
                    </tr>
                    <tr> --}}
                      <th>Date</th>
                      <th>In Time</th>
                      <th>Out Time</th>
                      <th>User</th>
                      <th>Dealer</th>
                      {{-- <th>This Dealer Hours</th>
                      <th>Total Hours Today</th>
                      <th>Hours This Month</th> --}}
                    </tr>
  
                  </thead>
  @php
    //   dd($late1);
  @endphp
                  <tbody>
                    @foreach ($late1 as $item)
  <tr>
     
      <td>{{$item->date}}</td>
      <td>{{$item->in_time}}</td>
      <td>{{$item->out_time}}</td>
      <td>{{get_name($item->user_id)}}</td>
      <td>{{get_name($item->dealer_id)}}</td>
      {{-- <td>{{$item->date}}</td>
      <td>{{$item->date}}</td>
      <td>{{$item->date}}</td> --}}
     
  
  </tr>
  @endforeach
                  </tbody>
                  <tfoot>
                  </tfoot>
                </table>
              </div>
          </div><!-- /.box-body -->
        </div><!-- /.box -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@if ($onlycontent == 1)
    {{-- {{dd("hello")}} --}}
@endif

<script>
  $('#firm_id').on('change', function () {
    $("#form1").submit();
  });
  $('#asm_id').on('change', function () {
    $("#form1").submit();
  });
  $('#dealer_id').on('change', function () {
    $("#form1").submit();
  });
  $('#interval').on('change', function () {
    $("#form1").submit();
  });
</script>

@endsection