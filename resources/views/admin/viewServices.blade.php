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
          <h1>
            Staff Management
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('/admin/staff_management')}}"><i class="fa fa-users"></i> Staff Management <span class="active">({{get_name($id)}})</span></a></li>
            <li class="active">View Services</li>
          </ol>
        </section>
        <!-- Main content -->

        <section class="content">
        	<div class="row">
        		<div class="col-md-9">
		          <div class="box">
                <div class="box-header">
                  <h3 class="box-title">View Services</h3>
                </div><!-- /.box-header -->
		            <div class="box-body">
		              <!-- THE CALENDAR -->
		              <div id="calendar"></div>
		            </div>
		            <!-- /.box-body -->
		          </div>
		          <!-- /. box -->
		        </div>			
			    </div><!-- /.row -->
		    </section><!-- /.content -->

    </div><!-- /.content-wrapper -->

<script>
  $(function () {
    //Date for the calendar events (dummy data)
    var date = new Date();
    var d = date.getDate(),
        m = date.getMonth(),
        y = date.getFullYear();

    function todate(job_date){
    	var jd = new Date(job_date);
    	return jd.toDateString();
    }

    $('#calendar').fullCalendar({ 
      header: {
        left: 'prev,next today',
        center: 'title',
        right: ''
      },
      buttonText: {
        today: 'today',
        month: 'month',
        week: 'week',
        day: 'day'
      },
      //Random default events
      events: [
      <?php foreach ($result as $value) {
      ?>
      		{
	          title: "Total : <?php echo $value->total;?>",
	          start: todate('<?php echo $value->job_date;?>'),
	          backgroundColor: "#00a65a", //red
	          borderColor: "#00a65a" //red
	        },      		
      <?php
      	}
      ?>        
      ],
      showNonCurrentDates: false,
      editable: false,
      droppable: false, // this allows things to be dropped onto the calendar !!!
      
    });

  });
</script>  
@endsection