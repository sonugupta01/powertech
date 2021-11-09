@extends('layouts.dashboard')
@section('content')

<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Reports
            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Reports</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="tab-button-outer">
              <ul id="tab-button">
                <li class="">
                  <a href="#tab02">MIS</a>
                </li>
              </ul>
            </div>
            <div class="tab-select-outer">
              <select id="tab-select">
                <option value="#tab02" {{ empty($tabName) || $tabName == 'tab02' ? 'selected' : '' }}>MIS</option>
                
              </select>
            </div>
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header" style="text-align: center; margin: 0 auto;">
                  <h3 class="box-title">Reports List</h3>
                </div><!-- /.box-header -->
              <div class="tab-contents" id="tab02">
                <div class="box-body ">
                  <form action="" class="" method="GET" id="misreportform"> 
                    <div class="row">
                      <div class="col-xs-12 col-md-offset-2 col-md-8">
                        <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                          <label>Firms</label>
                          <select name="firm" class="form-control" id="firm">
                            <option value="">Select Firms</option>
                            @foreach($firms as $firm)
                              <option value="{{$firm->id}}" @if(@$oldFirm == $firm->id) {{'selected'}} @endif>{{$firm->firm_name}}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                          <label>ASM</label>
                          <select name="asm" class="form-control" id="asm">
                            <option value="">Select ASM</option>
                            @foreach($asms as $asm)
                              <option value="{{$asm->id}}" @if(@$oldAsm == $asm->id) {{'selected'}} @endif>{{$asm->name}}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                          <label>OEM</label>
                          <select name="oem" class="form-control" id="oem">
                            <option value="">Select OEM</option>
                            @foreach($oems as $val)
                              <option value="{{$val->oem_id}}" @if(@$oldOem == $val->oem_id) {{'selected'}} @endif>{{get_oem_name($val->oem_id)}}</option>
                            @endforeach
                          </select>
                        </div>
                        {{-- <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                          <label>Group</label>
                          <select name="group" class="form-control">
                            <option value="">Select Group</option>
                            @foreach($groups as $val)
                              <option value="{{$val->id}}" @if(@$oldGroup == $val->id) {{'selected'}} @endif>{{$val->group_name}}</option>
                            @endforeach
                          </select>
                        </div> --}}
                        <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                          <label>Dealer</label>
                          <select class="form-control" id="dealer" name="dealer">
                            <option value="">Select Dealer</option>
                            @foreach($dealers_list as $value)
                            <option {{(@$oldDealer==$value->id)?'selected':''}} value="{{$value->id}}">{{$value->name}}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                          <label>Departments</label>
                          <select class="form-control" id="department" name="department">
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                            <option {{(@$oldDepartment==$department->id)?'selected':''}} value="{{$department->id}}">{{$department->name}}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                          <label>Brands</label>
                          <select class="form-control" id="brand" name="brand">
                            <option value="">Select Brand</option>
                            @foreach($brands as $value)
                            <option {{(@request()->brand==$value->id)?'selected':''}} value="{{$value->id}}">{{$value->brand_name}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      <hr style="clear: both;" />
                      <div class="col-xs-12 col-md-offset-2 col-md-8">
                          <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                            <label>Start Date</label>
                            <input type="text"  id="fromDate from" name="from1" placeholder="From Date" value="{{@$oldFromDate1}}" class="datePicker form-control" autocomplete="off" />
                          </div>
                          <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                            <label>End Date</label>
                            <input type="text" id="toDate to" name="to1" placeholder="To Date" value="{{@$oldToDate1}}" class="datePicker form-control" autocomplete="off" />
                          </div>
                          <div class="form-group report-field col-md-12 col-sm-12 col-xs-12" style="text-align: center;">
                              <label>or</label>
                          </div>
                          <div class="form-group report-field col-md-12 col-sm-12 col-xs-12">
                            <label>Select Month</label>
                            <input type="text"  id="selectMonth" name="month" value="{{@$oldSelectMonth}}" placeholder="Select Month" value="" class="datePicker1 form-control" autocomplete="off" />
                          </div>
                          
                          <div class="input-group form-group report-field col-md-12 col-sm-12 col-xs-12" style="text-align: center;">
                            <input class="btn btn-primary" type="submit" value="Submit">
                          </div>
                      </div>
                    </div>
                  </form>
                	<div class="table-responsive">
  	                <table class="table table-bordered table-striped report-table mis-table">
  	                  <thead>
  	                    <tr>
  	                      <th style="font-size: 12px;">Total Records: {{count($mis)}}</th>
  	                      <th colspan="10" style="text-align:left;">MIS</th>
  	                      <th colspan="7">
  	                        <button id="download2" class="btn btn-success">Download</button>
  	                      </th>
  	                    </tr>
  	                    <tr>
  	                      <th>CDC</th>
  	                      <!-- <th>Cust Bill</th> -->
  	                      <th>Actual Bill</th>
  	                      <th>Dealer Price</th>
  	                      <th>PT Share</th>
  	                      <th>Incentive</th>
                          <th>MTD LVT</th>
                          <th>MTD LVT Value</th>
                          <th>MTD LVT %</th>  	                      
                          <th>MTD MVT</th>
                          <th>MTD MVT Value</th>
                          <th>MTD MVT %</th>
                          <th>MTD HVT</th>
                          <th>MTD HVT Value</th>
                          <th>MTD HVT %</th>  	                      
  	                      <th>Total RO</th>
  	                      <th>Business per RO</th>
  	                      <th>Business per Treatment</th>
  	                      <th>RO Conversion Ratio</th>
  	                    </tr>
  	                    <?php $cp=$ap=$dp=$pts=$in=$lvt=$mtd_lvt=$mvt=$mtd_mvt=$hvt=$mtd_hvt=$service=0;
  	                     foreach ($mis as $val) {
                            $cp=$cp+round(@$val['customer_price']);
                            $ap=$ap+round(@$val['actual_price']);
                            $dp=$dp+round(@$val['dealer_price']);
                            $pts=$pts+round(@$val['powertech_share_price']);
                            $in=$in+round(@$val['incentive']);
                            $lvt=$lvt+round(@$val['lvt_total']);
                            $mtd_lvt=$mtd_lvt+round(@$val['mtd_lvt_value']);
                            $mvt=$mvt+round(@$val['mvt_total']);
                            $mtd_mvt=$mtd_mvt+round(@$val['mtd_mvt_value']);
                            $hvt=$hvt+round(@$val['hvt_total']);
                            $mtd_hvt=$mtd_hvt+round(@$val['mtd_hvt_value']);
                            $service=$service+round(@$val['service_load']);
                        }
                          
  	                     ?>

  	                     <tr>
  	                      <th>Business Total</th>
  	                      <!-- <th>{{$cp}}</th> -->
  	                      <th>{{$ap}}</th>
  	                      <th>{{$dp}}</th>
  	                      <th>{{$pts}}</th>
  	                      <th>{{$in}}</th>
                          <th>{{$lvt}}</th>
                          <th>{{$mtd_lvt}}</th>
                          <th>{{hvt_in_percentage($mtd_lvt,$ap)}}%</th>
                          <th>{{$mvt}}</th>
                          <th>{{$mtd_mvt}}</th>
                          <th>{{hvt_in_percentage($mtd_mvt,$ap)}}%</th>
  	                      <th>{{$hvt}}</th>
  	                      <th>{{$mtd_hvt}}</th>
                          <th>{{hvt_in_percentage($mtd_hvt,$ap)}}%</th>
  	                      <th>{{$service}}</th>
                          <th></th>
                          <th></th>
                          <th></th>
  	                    </tr>
  	                  </thead>
  	                  <tbody>
  	                    <?php 
                          if(count($mis)>0) {
  	                        foreach($mis as $val) {
                              $business_per_ro = $business_per_treatment =  $ro_ratio = 0; 
                              if(@$val['service_load']>0){
                                $business_per_ro = round(@$val['actual_price'])/@$val['service_load'];
                                $ro_ratio = @$val['total_job_done']/@$val['service_load']*100;
                              }

                              if(@$val['mtd_total']>0){
                                $business_per_treatment = round(@$val['actual_price'])/@$val['mtd_total'];
                              }
                              
                              
                        ?>
  	                    <tr>
  	                      <td style="background-color:#FFFF00; width: 275px;">{{get_name(@$val['dealer_id'])}}</td>
  	                      <!-- <td style="background-color:#B6DDE8;">{{round(@$val['customer_price'])}}</td> -->
  	                      <td style="background-color:#B6DDE8;">{{round(@$val['actual_price'])}}</td>
  	                      <td style="background-color:#F7FED0;">{{round(@$val['dealer_price'])}}</td>
  	                      <td style="background-color:#F7FED0;">{{round(@$val['powertech_share_price'])}}</td>
  	                      <td style="background-color:#FFFF00;">{{round(@$val['incentive'])}}</td>
                          <td style="background-color:#F2DDDC;">{{round(@$val['lvt_total'])}}</td>
                          <td style="background-color:#F2DDDC;">{{round(@$val['mtd_lvt_value'])}}</td>
                          <td style="background-color:#FFFF00;">{{hvt_in_percentage(@$val['mtd_lvt_value'],@$val['actual_price'])}}%</td>
                          <td style="background-color:#F2DDDC;">{{round(@$val['mvt_total'])}}</td>
                          <td style="background-color:#F2DDDC;">{{round(@$val['mtd_mvt_value'])}}</td>
                          <td style="background-color:#FFFF00;">{{hvt_in_percentage(@$val['mtd_mvt_value'],@$val['actual_price'])}}%</td>
  	                      <td style="background-color:#F2DDDC;">{{round(@$val['hvt_total'])}}</td>
                          <td style="background-color:#F2DDDC;">{{round(@$val['mtd_hvt_value'])}}</td>
  	                      <td style="background-color:#FFFF00;">{{hvt_in_percentage(@$val['mtd_hvt_value'],@$val['actual_price'])}}%</td>
  	                      <td style="background-color:#B6DDE8;">{{@$val['service_load']}}</td>
                          <td style="background-color:#F7FED0;">{{number_format((float)@$business_per_ro, 2, '.', '')}}</td>
                          <td style="background-color:#B6DDE8;">{{@$business_per_treatment}}</td>
                          <td style="background-color:#B6DDE8;">{{number_format((float)@$ro_ratio, 2, '.', '')}}</td>
  	                    </tr>
  	                    
  	                  <?php
                            }
                          } else {
                      ?>
  	                    <tr>
  	                      <td colspan="18">No Record</td>                          
  	                    </tr>
  	                  <?php } ?>
  	                  </tbody>
  	                </table>
                  </div>
                </div><!-- /.box-body -->
              </div>
              </div><!-- /.box -->
            </div><!-- /.col -->
          </div><!-- /.row -->
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
        
      <form method="get" id="form32" action="{{url('/admin/downloadMIS')}}">
        <input type="hidden" name="firm" value="{{@$oldFirm}}">
        <input type="hidden" name="asm" value="{{@$oldAsm}}">
        <input type="hidden" name="oem" value="{{@$oldOem}}">
        <input type="hidden" name="dealer" value="{{@$oldDealer}}">
        <input type="hidden" name="department" value="{{@$oldDepartment}}">
        <input type="hidden" name="brand" value="{{@request()->brand}}">
        <input type="hidden" name="from12" value="{{@$oldFromDate1}}">
        <input type="hidden" name="to12" value="{{@$oldToDate1}}">
        <input type="hidden" name="selectMonth2" value="{{@$oldSelectMonth}}">
      </form>  
<script type="text/javascript">

  
  $(document).on('click','#download2',function(){
    $('#form32').submit();
  });

  $(document).on('change','.datePicker',function(){
    $('.datePicker1').val('');
  });

  $(document).on('change','.datePicker1',function(){
    $('.datePicker').val('');
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
  // $tabButtonItem.first().addClass(activeClass);
  $tabContents.not(':first').hide();

  $tabSelect.on('change', function() {
    var target = $(this).val(),
    targetSelectNum = $(this).prop('selectedIndex');
    $tabButtonItem.removeClass(activeClass);
    $tabButtonItem.eq(targetSelectNum).addClass(activeClass);
    $tabContents.hide();
    $(target).show();
  });
});

$('#tab-button li a').on('click', function(e) {
  e.preventDefault();
  var $tabButtonItem = $('#tab-button li'),
  $tabSelect = $('#tab-select'),
  $tabContents = $('.tab-contents'),
  activeClass = 'is-active';
  var target = $(this).attr('href');   
  $tabButtonItem.removeClass(activeClass);
  $(this).parent().addClass(activeClass);
  $tabSelect.val(target);
  $tabContents.hide();
  $(target).show();
});

$('#firm').on('change',function(argument) {
  $('#asm').val('');
  $('#oem').val('');
  $('#dealer').val('');
  $('#misreportform').submit();
});

$('#asm').on('change',function(argument) {
  $('#oem').val('');
  $('#dealer').val('');
  $('#misreportform').submit();
});

$('#oem').on('change',function(argument) {
  $('#dealer').val('');
  $('#misreportform').submit();
});

$('#dealer').on('change',function(argument) {
  $('#department').val('');
  $('#misreportform').submit();
});

$('#department').on('change',function(argument) {
  $('#misreportform').submit();
});

$(document).ready(function(argument) {
  $('#firm').on('change',function(){
    var firm_id = $(this).val();
    $.ajax({
      url: "{{ url('admin/getAsmByfirm') }}/" + firm_id,
      method: 'GET',
      success: function(data){
        $("#asm").html(data.html);
        return false;
      },
    });
  });
});
</script> 
@endsection