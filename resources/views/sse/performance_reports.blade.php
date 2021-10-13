@extends('layouts.dashboard')
@section('content')

<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Performance Reports
            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/sse')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Performance Reports</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="tab-button-outer">
              <ul id="tab-button" class="tab-button1">
                <li>
                  <a href="#tab01">Advisor Performance wise report</a>
                </li>
                <!-- <li>
                  <a href="#tab02">Treatment wise report</a>
                </li> -->
                <!-- <li>
                  <a href="#tab03">Treatment wise report</a>
                </li>
                <li>
                  <a href="#tab04">Treatment wise report-Monthly</a>
                </li> -->
              </ul>
            </div>
            <div class="tab-select-outer">
              <select id="tab-select">
                <option value="#tab01">Advisor Performance wise report</option>
                <option value="#tab02">Treatment wise report</option>
                <!-- <option value="#tab03">Treatment wise report</option>
                <option value="#tab04">Treatment wise report-Monthly</option> -->
              </select>
            </div>
            <?php 
                if(@$oldMonth){
                  $month=$oldMonth;
                }else{
                  $month=date('Y-m');
                } 
            ?>
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header" style="text-align: center; margin: 0 auto;">
                  <h3 class="box-title">Reports List</h3>
                </div><!-- /.box-header -->
                <div id="tab01" class="tab-contents">
                    <form action="" class="" method="GET"> 
                      <div class="row">
                        <div class="col-xs-12 col-md-offset-2 col-md-8">
                            <div class="form-group report-field col-md-6 ol-sm-6 ol-xs-12">
                              <label>Dealer</label>
                              <select class="form-control" name="dealer">
                                  <option value="">Select Dealer</option>
                                @foreach($dealers as $value)
                                  <option {{(@$oldDealer==$value->id)?'selected':''}} value="{{$value->id}}">{{$value->name}}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="input-group form-group report-field col-md-6 ol-sm-6 ol-xs-12">
                              <label>Select Month</label>
                              <input type="text"  id="selectMonth" name="selectMonth" placeholder="Select Month" value="{{$month}}" class="datePicker1 form-control" autocomplete="off" />
                            </div>
                              <div class="form-group report-field col-md-12 ol-sm-12 ol-xs-12" style="text-align: center;">
                                <input class="btn btn-primary btn-div" type="submit" value="Submit">
                              </div>
                        </div>
                      </div>
                    </form>
                    <div class="box-body" style="overflow: auto;">
                      <div class="table-resposive">
                        <table class="table table-bordered table-striped report-table">
                          <thead>
                            <tr>
                              <th colspan="5" style="font-size: 12px;">Total Records: {{count($advisors)}}</th>
                              <th><button id="download" class="btn btn-success" style="margin: 0 auto; display: block;">Download</button></th>
                              <th><button id="downloadHVT" class="btn btn-success" style="margin: 0 auto; display: block;">Download Dealer Wise</button></th>
                            </tr>
                            <tr>
                              <th>Dealer</th>
                              <th>Advisor</th>
                              <th>PAN Card</th>
                              <th>Customer Billing</th>
                              <th>Incentive</th>
                              <th>HVT Value</th>
                              <th>HVT Numbers</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if(count($dealers)>0){
                            ?> 
                            @foreach($advisors as $value)
                              <tr>
                                <td>{{get_name($value->dealer_id)}}</td>
                                <td>{{get_advisor_name($value->advisor_id)}}</td>
                                <td>{{get_pan_no($value->advisor_id)}}</td>
                                <td>{{round($value->customer_price)}}</td>
                                <td>{{round($value->incentive)}}</td>
                                <td>{{round($value->hvt_value)}}</td>
                                <td>
                                    <a class="btn btn-success" href="{{url('/sse/downloadAdvisor')}}/{{$value->advisor_id}}/{{$value->dealer_id}}/{{@$month}}">Download</a>
                                </td>
                              </tr>
                            @endforeach                          
                            <?php  
                             }else{?>
                              <tr>
                                <td colspan="7">No Record</td>                          
                              </tr>
                            <?php }?>
                          </tbody>
                          <tfoot>
                            
                          </tfoot>
                        </table>
                        </div>
                    </div>
                </div>
                <div class="box-body tab-contents" id="tab02">
                  <form action="" class="" method="GET"> 
                      <div class="row">
                        <div class="col-xs-4 col-md-offset-2 col-md-8">
                            <div class="form-group report-field col-md-6">
                              <label>Dealer</label>
                              <select class="form-control" name="dealer">
                                  <option value="">Select Dealer</option>
                                @foreach($dealers as $value)
                                  <option {{(@$oldDealer==$value->id)?'selected':''}} value="{{$value->id}}">{{$value->name}}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="input-group report-field col-md-6">
                              <label>Select Month</label>
                              <input type="text"  id="selectMonth1" name="selectMonth" placeholder="Select Month" value="" class="datePicker1 form-control" autocomplete="off" />
                            </div>
                              <div class="form-group report-field col-md-12" style="text-align: center;">
                                <input class="btn btn-primary" type="submit" value="Submit">
                              </div>
                        </div>
                      </div>
                    </form>
                    <table class="table table-bordered table-striped  report-table" >
                      <thead>
                        <tr>
                          <th colspan="2" style="text-align: center;">Treatment Wise Report</th>
                          <!-- <th>
                            <form action="">
                              <input type="hidden" name="download" value="mis_wise">
                              <input class="btn btn-success" type="submit" value="Download">
                            </form>
                          </th> -->
                        </tr>
                        <tr>
                          <th>Dealer</th>
                          <th>Number and Value</th>
                        </tr> 
                      </thead>
                      <tbody>
                        <?php if(count($advisors)>0){ ?>

                      <?php }else{ ?>
                        <tr>
                          <td colspan="2">No Record</td>                          
                        </tr>
                      <?php } ?>
                      </tbody>
                    </table>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div><!-- /.col -->
          </div><!-- /.row -->
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
<form method="get" id="form1" action="{{url('/sse/downloadPerformanceSheet')}}">
  <input type="hidden" name="selectMonth1" value="{{@$month}}">
  <input type="hidden" name="dealer1" value="{{@$oldDealer}}">
</form> 
<form method="get" id="form2" action="{{url('/sse/downloadAllAdvisor')}}">
  <input type="hidden" name="selectMonth2" value="{{@$month}}">
  <input type="hidden" name="dealer2" value="{{@$oldDealer}}">
</form>  
<script type="text/javascript">
  $(document).on('click','#download',function(){
    $('#form1').submit();
  });
  $(document).on('click','#downloadHVT',function(){
    var selectdealer = $('select[name="dealer"]').val();
    if (selectdealer == undefined || selectdealer.length <= 0) {
      alert('Please select Dealer');
      return false;
    }else{
      $('input[name="dealer2"]').val(selectdealer);
      $('#form2').submit();
    }
    
    
  });
  	$(document).on('click', '.datePicker', function(){
    	$(this).datepicker({
	        autoclose: true,
	        format: 'yyyy-mm-dd'
    	}).focus();
   	});
  	$(document).on('click', '.datePicker1', function(){
	    $(this).datepicker({
	        autoclose: true,
	        format: "yyyy-mm",
	        startView: "months", 
	        minViewMode: "months"
	    }).focus();
	});
  	$(function() {
	  var $tabButtonItem = $('#tab-button li'),
	      $tabSelect = $('#tab-select'),
	      $tabContents = $('.tab-contents'),
	      activeClass = 'is-active';
	  $tabButtonItem.first().addClass(activeClass);
	  $tabContents.not(':first').hide();
	  $tabButtonItem.find('a').on('click', function(e) {
	    var target = $(this).attr('href');
	    $tabButtonItem.removeClass(activeClass);
	    $(this).parent().addClass(activeClass);
	    $tabSelect.val(target);
	    $tabContents.hide();
	    $(target).show();
	    e.preventDefault();
	  });
	  $tabSelect.on('change', function() {
	    var target = $(this).val(),
	        targetSelectNum = $(this).prop('selectedIndex');
	    $tabButtonItem.removeClass(activeClass);
	    $tabButtonItem.eq(targetSelectNum).addClass(activeClass);
	    $tabContents.hide();
	    $(target).show();
	  });
	});
</script> 
@endsection