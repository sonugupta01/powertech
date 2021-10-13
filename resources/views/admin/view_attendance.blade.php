@extends('layouts.dashboard')
@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Attendance
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{ url('/admin') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Attendance</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      
      <div class="col-xs-12">
        
        <div class="box">
          <div class="box-header" style="text-align: center; margin: 0 auto;">
            <h3 class="box-title">Attendance Report</h3>
          </div>
          <div class="row">
            <div class="col-md-6">
              <form method="GET" action="">
                <div class="col-md-4">
                  <input type="text"  id="selectMonth" name="selectMonth" value="{{(@$_GET['selectMonth']) ? @$_GET['selectMonth'] : date('Y-m')}}" placeholder="Select Month" value="" class="datePicker1 form-control" autocomplete="off" />
                </div>
                <div class="col-md-2">
                  <input class="btn btn-primary" type="submit" value="Submit">
                </div>
                <div class="col-md-2">
                  <a class="btn btn-success" href="{{URL::current()}}?selectMonth={{@$_GET['selectMonth']}}&download=1">Download</a>
                </div>
              </form>
              <div class="col-md-2">
                <a href="{{ URL::previous() }}" class="btn btn-info floatright" style="margin-top: -14px;">Back</a>
              </div>
            </div>
          </div>
          <br>
        </div>
        
        
        <div id="tab01" class="tab-contents">
          
          <div class="box-body" style="overflow: auto;">
            <div class="table-resposive">
              <table  class="table table-bordered table-striped report-table">
                <tr>
                  <th>Name:</th>
                <td>{{get_name($id)}}</td>
                </tr>
                <tr>
                  <th>Employee Code:</th>
                  <td>{{get_emp_code($id)}}</td>
                </tr>
                <tr>
                  <th>Current Dealer:</th>
                  <td>{{get_dealer_name_by_id($id)}}</td>
                </tr>
              </table>
              <table class="table table-bordered table-striped report-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>In Time</th>
                    <th>Out Time</th>
                    <th>User</th>
                    <th>Dealer</th>
                    <th>This Dealer Hours</th>
                    <th>Total Hours Today</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $full = 0;
                  $half = 0;
                  $three = 0;
                  $notmarked = 0;
                  $dates = [];
                  ?>
                  @foreach ($result as $key1 => $value1)
                  <?php  $row = count($result[$key1])?>
                  @foreach ($result[$key1] as $key => $value)
                  <tr>
                    @if ($key == 0)
                    <td rowspan="{{$row}}" style="vertical-align: middle">{{ date('d M, Y', strtotime($result[$key1][0]->date)) }}</td>                    
                    @endif
                    
                    @if (isset($value->user_id))
                    <td>{{ @$value->in_time }}</td>
                    <td>{{ @$value->out_time }}</td>
                    <td>{{ get_name($value->user_id) }}</td>
                    <td>{{ get_name($value->dealer_id) }}</td>
                    <td>
                      <?php
                      $timeFirst = strtotime($value->in_time);
                      $timeSecond = strtotime($value->out_time);
                      if(!empty($timeFirst) && !empty($timeSecond)){
                        @$differenceInSeconds = secToHR($timeSecond - $timeFirst);
                        echo  @$differenceInSeconds['hours'] . ' hours ' . @$differenceInSeconds['minutes'] . ' minutes'  ;                   
                      }else{
                        $notmarked += 1; 
                        echo 'Attendance Not Marked';                      
                      }
                      ?>
                    </td>
                    
                    @if ($row > 1)
                    @if ($key == 0)
                    <td rowspan="{{$row}}" style="vertical-align: middle">
                      <?php
                      if(!empty($timeFirst) && !empty($timeSecond)){
                        @$data_total_hours = day_hours($value->user_id, $value->date);
                        echo @$data_total_hours['hours'] . ' hours ' . 
                        @$data_total_hours['minutes'] . ' minutes';
                        
                        if ((@$data_total_hours['hours'] < 6 && (@$data_total_hours['hours'] >= 3) ) ) {
                          $half += 1;
                        }elseif(@$data_total_hours['hours'] > 6) {
                          $full += 1;
                        }
                        
                      }else{
                        echo 'Attendance Not Marked';
                      }
                      
                      ?>
                    </td>                   
                    @endif
                    @else
                    <td>
                      <?php
                      if(!empty($timeFirst) && !empty($timeSecond)){
                        @$data_total_hours = day_hours($value->user_id, $value->date);
                        echo @$data_total_hours['hours'] . ' hours ' .
                        @$data_total_hours['minutes'] . ' minutes';
                        
                        if ((@$data_total_hours['hours'] < 6 && (@$data_total_hours['hours'] >= 3) ) ) {
                          $half += 1;
                        }elseif(@$data_total_hours['hours'] > 6) {
                          $full += 1;
                        }elseif(@$data_total_hours['hours'] < 3){
                          $three += 1;
                        }
                        
                      }else{
                        echo 'Attendance Not Marked';
                      }
                      ?>
                    </td> 
                    @endif
                    
                    @else
                    <td colspan="6" style="text-align: center;background-color:#dd4b39;">Absent</td>
                    @endif
                  </tr>   
                  @endforeach 
                  
                  @endforeach
                  <tr>
                    <td colspan="5" style="text-align: center">
                      Total Hours This Month : 
                    </td>
                    <td colspan="2" style="text-align: center"><?php
                      $month = ((@$_GET['selectMonth']) ? date('m',strtotime(@$_GET['selectMonth'])) : date('m'));
                      // dd($month);
                      @$mydata1 = month_hours($id,$month); 
                      ?>
                      {{ @$mydata1['hours'] . ' hours ' . @$mydata1['minutes'] . ' minutes' }}</td>
                      
                    </tr>
                    
                    
                    <tr>
                      <td colspan="5" style="text-align: center">
                        Total Full days : 
                      </td>
                      <td colspan="2" style="text-align: center">{{$full}} days</td>
                      
                    </tr>
                    
                    <tr>
                      <td colspan="5" style="text-align: center">
                        Total Half days : 
                      </td>
                      <td colspan="2" style="text-align: center">{{$half}} days</td>
                      
                    </tr>
                    
                    <tr>
                      <td colspan="5" style="text-align: center">
                        Working Hours Less Than 3 : 
                      </td>
                      <td colspan="2" style="text-align: center">{{ (@$three != "") ? @$three : 0 }} days </td>
                      
                    </tr>
                    
                    <tr>
                      <td colspan="5" style="text-align: center">
                        Days Attendance Not Marked : 
                      </td>
                      <td colspan="2" style="text-align: center">{{ (@$notmarked != "") ? @$notmarked : 0 }} days </td>
                      
                    </tr>
                    
                    @if (empty(count($result)))
                    <tr>
                      <td colspan="8">No Record</td>
                    </tr>                   
                    @endif
                    
                  </tbody>
                  <tfoot>
                  </tfoot>
                </table>
                <script>
                  $('#datepicker').datepicker({ autoclose: true, format: 'yyyy-mm-dd', endDate: '+0d' }); 
                  $(document).on('click', '.datePicker1', function(){
                    $(this).datepicker({
                      autoclose: true,
                      format: "yyyy-mm",
                      startView: "months", 
                      minViewMode: "months"
                    }).focus();
                  });
                </script>
              </div>
            </div>
          </div>
        </div><!-- /.box -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->


@endsection