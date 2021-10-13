@extends('layouts.dashboard')
@section('content')

<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Consumption Report
            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Consumption Report</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="tab-button-outer">
              <ul id="tab-button" class="tab-button1">
                <!-- <li>
                  <a href="#tab01">Consumption by Treatments</a>
                </li> -->
                <li>
                  <a href="#tab02">Consumption by Products</a>
                </li>
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
                <option value="#tab01">Consumption by Treatments</option>
                <option value="#tab02">Consumption by Products</option>
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
                  <h3 class="box-title">Report List</h3>
                </div><!-- /.box-header -->
                <!-- <div id="tab01" class="tab-contents">
                    <form action="" class="" method="GET"> 
                      <div class="row">
                        <div class="col-xs-12 col-md-offset-2 col-md-8">
                            <div class="form-group report-field col-md-6 ol-sm-6 ol-xs-12">
                              <label>Dealer</label>
                              <select class="form-control" name="dealer">
                                  <option value="">Select Dealer</option>
                                  @foreach($dealers as $dealer)
                                    <option value="{{$dealer->id}}" @if(@$oldDealer == $dealer->id) {{'selected'}} @endif>{{$dealer->name}}</option>
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
                              <th colspan="3" style="font-size: 12px;">Total Records: {{count($treatments)}}</th>
                              <th><button class="btn btn-success" style="margin: 0 auto; display: block;">Download</button></th>
                               <th><button id="download" class="btn btn-success" style="margin: 0 auto; display: block;">Download</button></th> -->
                              <!-- <th><button id="downloadHVT" class="btn btn-success" style="margin: 0 auto; display: block;">Download Dealer Wise</button></th>
                            </tr> 
                            <tr>
                              <th>Treatment</th>
                              <th>Products</th>
                              <th>Total Treaments</th>
                              <th>Total Price</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if(count($treatments)>0){
                            ?> 
                            @foreach($treatments as $value)
                              <tr>
                                <td>{{$value['treatment']}}</td>
                                <td>
                                  @if(!empty($value['products']))
                                    <table class="table table-bordered table-striped report-table">
                                      <tr>
                                        <th>Product</th>
                                        <th>Total Quantity</th>
                                        <th>Total Price</th>
                                      </tr>
                                      @foreach($value['products'] as $val)
                                      <tr>
                                        <td>{{$val['name']}}</td>
                                        <td>{{$val['total_quantitys']}} {{get_uom($val['uom'])}}</td>
                                        <td>{{$val['total_prices']}}</td>
                                      </tr>
                                      @endforeach
                                    </table>
                                  @endif
                                </td>
                                <td>{{$value['count']}}</td>
                                <td>{{$value['total_price']}}</td>
                              </tr>
                            @endforeach                          
                            <?php  
                             }else{?>
                              <tr>
                                <td colspan="4">No Record</td>                          
                              </tr>
                            <?php }?>
                          </tbody>
                          <tfoot>
                            
                          </tfoot>
                        </table>
                        </div>
                    </div>
                </div> -->
                <div class="box-body tab-contents" id="tab02">
                <form action="" class="" method="GET"> 
                      <div class="row">
                        <div class="col-xs-12 col-md-offset-2 col-md-8">
                            <div class="form-group report-field col-md-6 ol-sm-6 ol-xs-12">
                              <label>Dealer</label>
                              <select class="form-control" name="dealer">
                                  <option value="">Select Dealer</option>
                                  @foreach($dealers as $dealer)
                                    <option value="{{$dealer->id}}" @if(@$oldDealer == $dealer->id) {{'selected'}} @endif>{{$dealer->name}}</option>
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
                    <table class="table table-bordered table-striped  report-table" >
                      <thead>
                        <tr>
                          <th colspan="3" style="text-align: center;">Consumption by Products</th>
                          <!-- <th>
                            <form action="">
                              <input type="hidden" name="download" value="mis_wise">
                              <input class="btn btn-success" type="submit" value="Download">
                            </form>
                          </th> -->
                        </tr>
                        <tr>
                          <th>Product</th>
                          <th>Total Quantity</th>
                          <th>Total Price</th>
                        </tr> 
                      </thead>
                      <tbody>
                        <?php if(count($products)>0){ ?>
                          @foreach($products as $pro)
                            <tr>
                              <td>{{$pro->name}}</td>
                              <td>{{$pro->total_quantity}} {{get_uom($pro->uom)}}</td>
                              <td>{{$pro->total_price}}</td>
                            </tr>
                          @endforeach
                      <?php }else{ ?>
                        <tr>
                          <td colspan="3">No Record</td>                          
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
<form method="get" id="form1" action="{{url('/admin/downloadPerformanceSheet')}}">
  <input type="hidden" name="selectMonth1" value="{{@$month}}">
  <input type="hidden" name="dealer1" value="{{@$oldDealer}}">
</form> 
<form method="get" id="form2" action="{{url('/admin/downloadAllAdvisor')}}">
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