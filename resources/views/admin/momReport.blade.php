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
          <li class="is-active">
            <a href="#tab01">MOM Report</a>
          </li>
        </ul>
      </div>
      <div class="tab-select-outer">
        <select id="tab-select">
          <option value="#tab01">MOM Report</option>
        </select>
      </div>
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header" style="text-align: center; margin: 0 auto;">
            <h3 class="box-title">Reports List</h3>
          </div><!-- /.box-header -->
          <div id="tab01" class="tab-contents">
            <form action="" class="" method="GET" id="dailyreportform"> 
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
                    <select name="oem" class="form-control">
                      <option value="">Select OEM</option>
                      @foreach($oems as $val)
                        <option value="{{$val->oem_id}}" @if(@$oldOem == $val->oem_id) {{'selected'}} @endif>{{get_oem_name($val->oem_id)}}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>Group</label>
                    <select name="group" class="form-control">
                      <option value="">Select Group</option>
                      @foreach($groups as $val)
                        <option value="{{$val->group_id}}" @if(@$oldGroup == $val->group_id) {{'selected'}} @endif>{{get_group_name($val->group_id)}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <hr style="clear: both;" />
                <div class="col-md-offset-2 col-md-8 col-sm-12 resport-tabs" >
                    <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                      <label>Start Date</label>
                      <input type="text"  id="fromDate from" name="from" placeholder="From Date" value="{{@$oldFromDate}}" class="datePicker form-control" autocomplete="off" />
                    </div>
                    <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                      <label>End Date</label>
                      <input type="text" id="toDate to" name="to" placeholder="To Date" value="{{@$oldToDate}}" class="datePicker form-control" autocomplete="off" />
                    </div>
                    <div class="form-group report-field col-md-12 col-sm-12 col-xs-12" style="text-align: center;">
                        <label>or</label>
                    </div>
                    <div class="form-group report-field col-md-12 col-sm-12 col-xs-12">
                        <label>Select Month</label>
                        <input type="text" id="month1" name="month1" placeholder="Select Month" value="{{@$oldSelectMonth}}" class="datePicker1 form-control" autocomplete="off" />
                    </div>
                    <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                      <label>Dealer</label>
                      <select class="form-control" id="dealer" name="dealer">
                        <option value="">Select Dealer</option>
                        @foreach($dealers as $value)
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
                    {{-- <div class="form-group report-field col-md-4 col-sm-4 col-xs-12">
                      <label>Dealer's Advisors</label>
                      <select class="form-control" id="advisor" name="advisor">
                        <option value="">Select Advisor</option>
                        @foreach($allAdvisors as $advisor)
                        <option {{(@$oldAdvisor==$advisor->advisor_id)?'selected':''}} value="{{$advisor->advisor_id}}" style="font-size: 16px;">{{get_advisor_name($advisor->id)}} - {{get_dealer_name($advisor->dealer_id)}}</option>
                        @endforeach
                      </select>
                    </div> --}}


                    {{-- brand dropdown start filter --}}
                    <div class="form-group report-field col-md-12 col-sm-12 col-xs-12">
                      <label>Brands</label>
                      <select class="form-control" id="brand" name="brand">
                        <option value="">Select Brand</option>
                      </select>
                    </div>
                    {{-- brand dropdown end filter --}}


                    <div class="form-group report-field col-md-12 col-sm-12 col-xs-12">
                      <label>Report Type</label>
                      <div class="form-control required">
                        <input type="radio" {{(@$oldReport=='dealer')?'checked':''}} value="dealer" name="report_type" checked> Dealer Wise
                        <input type="radio" {{(@$oldReport=='advisor')?'checked':''}} value="advisor" name="report_type"> Advisor Wise
                        <input type="radio" {{(@$oldReport=='asm_id')?'checked':''}} value="asm_id" name="report_type"> ASM Wise
                        <input type="radio" {{(@$oldReport=='firm_id')?'checked':''}} value="firm_id" name="report_type"> Firm Wise
                      </div>
                    </div>
                    <div class="form-group report-field col-md-12 col-sm-12 col-xs-12" style="text-align: center;">
                      <input class="btn btn-primary" type="submit" value="Submit">   
                    </div>
                </div>
              </div>
            </form>
            <!-- <button class="btn btn-success" id="all_dealers" style="margin-left: 48px;">Download Whole Report</button> -->
            <div class="box-body table-responsive" style="overflow: auto;">
                <div class="col-md-12">
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped report-table">
                      <thead>
                        <tr>
                          <th>Advisor Name</th>
                          <th style="text-align: center;" colspan="5">MTD</th>
                          <th>
                          </th>
                        </tr>
                        <tr>
                          <td></td>
                          <th>Cust Billing</th>
                          <th>Actual Billing</th>
                          <th>Incentive</th>
                          <th>Cust Billing</th>
                          <th>Actual Billing</th>
                          <th>Incentive</th>
                        </tr>
                      </thead>
                      <tbody>
                          <tr>
                            <td colspan="7">No Record</td>                          
                          </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
            </div>
          </div>
        </div><!-- /.box -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<form method="get" id="form12" action="{{url('/admin/downloadReport')}}">
  <input type="hidden" name="from1" value="{{@$oldFromDate}}">
  <input type="hidden" name="to1" value="{{@$oldToDate}}">
  <input type="hidden" name="dealer1" value="{{@$oldDealer}}">
  {{-- <input type="hidden" name="advisor1" value="{{@$oldAdvisor}}"> --}}
  <input type="hidden" name="department1" value="{{@$oldDepartment}}">
  <input type="hidden" name="month3" value="{{@$oldSelectMonth}}">
  <input type="hidden" name="report" value="dealer">
  <input type="hidden" name="download" value="dealer_wise">
</form> 
<form method="get" id="form22" action="{{url('/admin/downloadReport')}}">
  <input type="hidden" name="from2" value="{{@$oldFromDate}}">
  <input type="hidden" name="to2" value="{{@$oldToDate}}">
  <input type="hidden" name="dealer2" value="{{@$oldDealer}}">
  {{-- <input type="hidden" name="advisor2" value="{{@$oldAdvisor}}"> --}}
  <input type="hidden" name="department2" value="{{@$oldDepartment}}">
  <input type="hidden" name="report" value="advisor">
  <input type="hidden" name="month4" value="{{@$oldSelectMonth}}">
  <input type="hidden" name="download" value="advisor_wise">
</form>
<form method="get" id="asmform" action="{{url('/admin/downloadReport')}}">
  <input type="hidden" name="asm_id" value="{{@$oldAsm}}">
  <input type="hidden" name="asm_from" value="{{@$oldFromDate}}">
  <input type="hidden" name="asm_to" value="{{@$oldToDate}}">
  <input type="hidden" name="asm_dealer" value="{{@$oldDealer}}">
  {{-- <input type="hidden" name="asm_advisor" value="{{@$oldAdvisor}}"> --}}
  <input type="hidden" name="asm_department" value="{{@$oldDepartment}}">
  <input type="hidden" name="asm_month" value="{{@$oldSelectMonth}}">
  <input type="hidden" name="report" value="asm">
  <input type="hidden" name="download" value="asm_wise">
</form>
<form method="get" id="firmform" action="{{url('/admin/downloadReport')}}">
  <input type="hidden" name="firm_id" value="{{@$oldFirm}}">
  <input type="hidden" name="firm_asm_id" value="{{@$oldAsm}}">
  <input type="hidden" name="firm_from" value="{{@$oldFromDate}}">
  <input type="hidden" name="firm_to" value="{{@$oldToDate}}">
  <input type="hidden" name="firm_dealer" value="{{@$oldDealer}}">
  {{-- <input type="hidden" name="firm_advisor" value="{{@$oldAdvisor}}"> --}}
  <input type="hidden" name="firm_department" value="{{@$oldDepartment}}">
  <input type="hidden" name="firm_month" value="{{@$oldSelectMonth}}">
  <input type="hidden" name="report" value="firm">
  <input type="hidden" name="download" value="firm_wise">
</form>
{{-- <form method="get" id="form32" action="{{url('/admin/downloadMIS')}}">
  <input type="hidden" name="from12" value="{{@$oldFromDate1}}">
  <input type="hidden" name="to12" value="{{@$oldToDate1}}">
  <input type="hidden" name="selectMonth2" value="{{@$oldMonth}}">
</form> --}} 
<form method="get" id="foralldealers" action="{{url('/admin/downloadAllDealerReport')}}">
  <input type="hidden" name="getMonth" value="">
  <input type="hidden" name="getReportType" value="">
</form> 
<script type="text/javascript">

  $(document).on('click','#download',function(){
    $('#form12').submit();
  });
  $(document).on('click','#download1',function(){
    $('#form22').submit();
  });
  $(document).on('click','#download3',function(){
    $('#asmform').submit();
  });
  $(document).on('click','#download4',function(){
    $('#firmform').submit();
  });
  // $(document).on('click','#download2',function(){
  //   $('#form32').submit();
  // });
  $(document).on('click','#all_dealers',function(){
    var mnth = $('#month1').val();
    var reportType = $('input[name=report_type]:checked').val();
    if(mnth == ''){
      alert('Please Select Month!');
    }else{
      $('input[name=getMonth]').val(mnth);
      $('input[name=getReportType]').val(reportType);
      $('#foralldealers').submit();
    }
    //$('#form32').submit();
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
//  $tabButtonItem.first().addClass(activeClass);
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
  $('#dailyreportform').submit();
});

$('#asm').on('change',function(argument) {
  $('#dailyreportform').submit();
});

$('#dealer').on('change',function(argument) {
  $('#dailyreportform').submit();
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