@extends('layouts.dashboard')
@section('content')
<style type="text/css">
  .fc-time{
    display: none;
  }
  .fc-day-grid-event>.fc-content{
    font-size: 13px;
  }
</style>
  <link href="{{ asset('css/fullcalendar.min.css') }}" rel="stylesheet" type="text/css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
  <script src="{{ asset('js/fullcalendar.min.js') }}"></script>
  <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          @if(Session::has('error'))
            <div class="alert alert-danger">{{ Session::get('error') }}</div>
          @endif
          @if(Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
          @endif
          <h1>
            Dashboard
            <small>Control panel</small>
          </h1>
        </section>
        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-md-12">
              <div class="col-md-6">
                <label style="float: right; font-size: 20px;">{{date('F Y',strtotime($currentM))}}</label>
              </div>
              <div class="col-md-6">
                <form method="GET" action="">
                  <div class="col-md-4">
                    <input type="text"  id="selectMonth" name="selectMonth" value="{{@$oldMonth}}" placeholder="Select Month" value="" class="datePicker1 form-control" autocomplete="off" />
                  </div>
                  <div class="col-md-2">
                    <input class="btn btn-primary" type="submit" value="Submit">
                  </div>
                </form>
              </div>
            </div>
          </div>
          <div class="row" style="margin-top: 20px;">
            <div class="col-md-9">
              <div class="box box-primary">
                <div class="box-body no-padding">
                  <!-- THE CALENDAR -->
                  <div id="calendar"></div>
                </div>
                <!-- /.box-body -->
              </div>
              <!-- /. box -->
            </div>
            <?php $vas_total=$vas_value=$hvt_total=$hvt_value=0;
                foreach ($result as $value) {
                  // $total=$total+$value['total'];
                  $vas_total=$vas_total+$value['vas_total'];
                  // $vas_value=$vas_value+$value['vas_value'];
                  $vas_value=$vas_value+$value['actual_price'];
                  $hvt_total=$hvt_total+$value['hvt_total'];
                  $hvt_value=$hvt_value+$value['hvt_value']; 
                } 
            ?>
            <div class="col-md-3">
                  <table style="width: 100%; border: 1px solid #ccc !important; background-color: #fff;" class="table-bordered calendar-table">
                    <tr>
                      <th colspan="2" style="text-align: center;">Monthly Treatments till Date</th>
                    </tr>
                    <tr class="btn-success">
                      <td>RO:</td>
                      <td>{{number_format(@$total)}}</td>
                    </tr>
                    <tr class="btn-danger">
                      <th colspan="2">VAS</th>
                    </tr>
                    <tr class="btn-success">
                      <td>No of Trmt:</td>
                      <td>{{number_format(@$vas_total)}}</td>
                    </tr>
                    <tr class="btn-success">
                      <td>Amount:</td>
                      <td>{{number_format(@$vas_value)}}</td>
                    </tr>
                    <tr style="background-color: #FFFF00;">
                      <th>Value Per Treatment</th>
                      <th>{{vas_in_percentage(@$vas_value,@$vas_total)}}</th>
                    </tr>
                    <tr class="btn-danger">
                      <th colspan="2">HVT</th>
                    </tr>
                    <tr class="btn-success">
                      <td>No of Trmt:</td>
                      <td>{{number_format(@$hvt_total)}}</td>
                    </tr>
                    <tr class="btn-success">
                      <td>Amount:</td>
                      <td>{{number_format(@$hvt_value)}}</td>
                    </tr>
                    <tr style="background-color: #FFFF00;">
                      <th>HVT %</th>
                      <th>{{hvt_in_percentage(@$hvt_value,@$vas_value)}}%</th>
                    </tr>
                    <tr>
                      <td colspan="2">
                        <button id="download" class="btn btn-success" style="margin: 0 auto; display: block;">Download</button>
                      </td>
                    </tr>
                  </table>
                  <!-- <div class="box box-primary" style=" margin-top: 10px;">
                    <div class="box-body">
                      <label style=" margin-left: 60px;">Add Service Load</label>
                      <form method="POST" action="{{url('/admin/addServiceLoad')}}">
                        <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                        <div class="form-group">
                          <label for="dealer_id">Dealer<span class="required-title">*</span></label>
                          <select class="form-control required" id="dealer_id" name="dealer_id">
                            <option value="">Select Dealer</option>
                            @foreach($dealers as $dealer)
                              <option value="{{$dealer->dealer_id}}">{{ucwords($dealer->dealer_name)}}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="form-group">
                          <label for="service_date">Service Date<span class="required-title">*</span></label>
                            <div class="input-group date">
                             <div class="input-group-addon">
                               <i class="fa fa-calendar"></i>
                             </div>
                             <input class="form-control pull-right" id="datepicker" value="{{ old('service_date') }}" type="text" name="service_date" required="">
                           </div>
                        </div>
                        <div class="form-group">
                          <label for="total_jobs">No. of Jobs<span class="required-title">*</span></label>
                          <input type="text" name="total_jobs" class="form-control required" value="{{ old('total_jobs') }}" id="total_jobs" placeholder="Enter no. of jobs" required="">
                        </div>
                        <div class="box-footer">
                          <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                      </form>
                    </div>
                  </div>  -->
            </div>
            <!-- <div class="caltable">
              <?php  
            /* sample usages */
            //echo '<h2><i class="fa fa-fw"></i>July 2018</h2>';
            //echo draw_calendar(7,2018);
          ?>  
            </div> -->        
          </div><!-- /.row -->
        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->
<form method="get" id="form1" action="{{url('/admin/downloadDashboard')}}">
  <input type="hidden" name="selectMonth1" value="{{@$oldMonth}}">
</form>
<script>
  $(document).on('click','#download',function(){
    $('#form1').submit();
  });
  $('#datepicker').datepicker({ autoclose: true, format: 'yyyy-mm-dd', endDate: '+0d' }); 
  $(document).on('click', '.datePicker1', function(){
     $(this).datepicker({
        autoclose: true,
        format: "yyyy-mm",
        startView: "months", 
        minViewMode: "months"
     }).focus();
   });
  $(function () {
    //alert(Date(<?php echo date('d-m-y');?>));
    //Date for the calendar events (dummy data)
    var date = new Date();
    var d = date.getDate(),
        m = date.getMonth(),
        y = date.getFullYear();
    function todate(job_date, time){
      var jd = new Date(job_date);
      var aa = job_date+'T'+time;
      //alert(aa);
      return aa;
    }
    $('#calendar').fullCalendar({
      header: {
        left: '',
        center: '',
        right: ''
      },
      buttonText: {
        today: 'today',
        month: 'month',
        week: 'week',
        day: 'day'
      },
      eventOrder: "start",
      //Random default events
      events: [
      <?php 
      foreach ($result as $value) {
      ?>
          {
            title: "RO: <?php echo $value['total'];?>",
            start: todate('<?php echo $value['job_date'];?>','01:00:00'),
            backgroundColor: "#00a65a", //red
            borderColor: "#00a65a", //red
            description: '1'
          },          
          {
            title: "VAS",
            start: todate('<?php echo $value['job_date'];?>','02:00:00'),
            backgroundColor: "#dd4b39", //red
            borderColor: "#dd4b39", //red
            description: '2'
          },
          {
            title: "No of Trmt: <?php echo number_format($value['vas_total']);?>",
            start: todate('<?php echo $value['job_date'];?>','03:00:00'),
            backgroundColor: "#00a65a", //red
            borderColor: "#00a65a", //red
            description: '3'
          },
          {
            // title: "Amount: <?php echo number_format($value['vas_value']);?>",
            title: "Amount: <?php echo number_format($value['actual_price']);?>",
            start: todate('<?php echo $value['job_date'];?>','04:00:00'),
            backgroundColor: "#00a65a", //red
            borderColor: "#00a65a", //red
            description: '4'
          },
          {
            title: "HVT",
            start: todate('<?php echo $value['job_date'];?>','05:00:00'),
            backgroundColor: "#dd4b39", //red
            borderColor: "#dd4b39", //red
            description: '5'
          },
          {
            title: "No of Trmt: <?php echo number_format($value['hvt_total']);?>",
            start: todate('<?php echo $value['job_date'];?>','06:00:00'),
            backgroundColor: "#00a65a", //red
            borderColor: "#00a65a", //red
            description: '6'
          },
          {
            title: "Amount: <?php echo number_format($value['hvt_value']);?>",
            start: todate('<?php echo $value['job_date'];?>','07:00:00'),
            backgroundColor: "#00a65a", //red
            borderColor: "#00a65a", //red
            description: '7'
          },
      <?php
        }
      ?>
      ],
      editable: false,
      droppable: false, // this allows things to be dropped onto the calendar !!!
      fixedWeekCount:false,
    });
    $('#calendar').fullCalendar('gotoDate', <?php echo $current; ?>);
    //$('.fc-other-month').html('');
  });
</script>  
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
   <!--  <script src="{{ asset('/js/dashboard.js') }}" type="text/javascript"></script> -->
@endsection