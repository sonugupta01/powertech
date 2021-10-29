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
            <a href="#tab01">Daily Report</a>
          </li>
        </ul>
      </div>
      <div class="tab-select-outer">
        <select id="tab-select">
          <option value="#tab01">Daily Report</option>
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
                  {{-- <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
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
                  </div> --}}
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
                        @foreach($brands as $value)
                        <option {{(@request()->brand==$value->id)?'selected':''}} value="{{$value->id}}">{{$value->brand_name}}</option>
                        @endforeach
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
              @if(Session::get('oldReport')=='firm_id')
                <table class="table table-bordered table-striped report-table">
                  <thead>
                    <?php 
                    $i=1;  
                    $total_cp=$total_ap=$total_diff=$total_dp=$total_pt_share=$total_int=0;
                    foreach($result4 as $value4) {
                      if ($value4['job_type'] == '5') {
                        $customer_price = $value4['customer_price'];
                        $actual_price = $value4['actual_price'];
                        $difference_price = $value4['difference_price'];
                        $powertech_share = 0;
                        $dealer_price = $value4['dealer_price'];
                        $incentive = $value4['incentive'];
                      } else {
                        $customer_price = 0;
                        $actual_price = 0;
                        $difference_price = 0;
                        $dealer_price = 0;
                        $powertech_share = 0;
                        $incentive = 0;
                      }
                      $total_cp= $total_cp+$customer_price;
                      $total_ap= $total_ap+$actual_price;
                      $total_diff= $total_diff+$difference_price;
                      $total_dp= $total_dp+$dealer_price;
                      $total_pt_share= $total_pt_share+$powertech_share;
                      $total_int= $total_int+$incentive;
                    
                    }  ?>
                    <tr>
                      <th style="text-align:center;">
                        @if(count($result4)>0)
                          <button id="download4" class="btn btn-success" value="firm_wise">Download</button>
                        @endif
                      </th>
                      <th colspan="8" style="font-size: 12px;">Total Record: {{count($result4)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_cp)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_ap)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_diff)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_dp)}}</th>
                      <th style="background-color: #EEECE1;">{{$total_pt_share}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_int)}}</th>
                      <th></th>
                    </tr>
                    <tr>
                      <th>Date</th>
                      <th>Dealer Name</th>
                      <th>Job Card</th>
                      <th>Bill No.</th>
                      <th>Regn No.</th>
                      <th>Advisor</th>
                      <th>Model</th>
                      <th>Treatment</th>
                      <th>Labour Code</th>
                      <th style="background-color: #FFFF00;">Customer Price</th>
                      <th style="background-color: #FFFF00;">Actual Price</th>
                      <th style="background-color: #FFFF00;">Difference</th>
                      <th style="background-color: #FFFF00;">Dealer Price</th>
                      <th style="background-color: #FFFF00;">PT Share</th>
                      <th style="background-color: #FFFF00;">Incentive</th>
                      <th>Remark</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if(count($result4)>0){               
                      foreach($result4 as $val4) {
                    ?>
                        <tr>
                          <td style="width: 100px;">{{date("d-M-Y", strtotime($val4['job_date']))}}</td>
                          <td>{{get_dealer_name($val4['dealer_id'])}}</td>
                          <td>{{$val4['job_card_no']}}</td>
                          <td>{{$val4['bill_no']}}</td>
                          <td>{{strtoupper($val4['regn_no'])}}</td>
                          <td>{{get_advisor_name($val4['advisor_id'])}}</td>
                          <td>{{get_model_name($val4['model_id'])}}</td>
                          <td>{{$val4['treatment_name']}}</td>
                          <td>{{$val4['labour_code']}}</td>
                          <td>{{round($val4['customer_price'])}}</td>
                          <td>{{round(@$val4['actual_price'])}}</td>
                          <td>{{round(@$val4['difference_price'])}}</td>
                          <td>{{round($val4['dealer_price'])}}</td>
                          <td>{{round($powertech_share)}}</td>
                          <td>{{round($val4['incentive'])}}</td>
                          <td>{{$val4['remarks']}}</td>
                        </tr>                            
                    <?php
                      $i++;
                      } 
                    }else{?>
                      <tr>
                        <td colspan="16">No Record</td>                          
                      </tr>
                    <?php }?>
                  </tbody>
                  <tfoot>
                  </tfoot>
                </table>
              @endif
              @if(Session::get('oldReport')=='asm_id')
                <table class="table table-bordered table-striped report-table">
                  <thead>
                  <?php 
                    $i=1;  
                    $total_cp=$total_ap=$total_diff=$total_dp=$total_pt_share=$total_int=0;
                    foreach($result3 as $value3) {
                        if (@$value3['job_type'] == '5') {
                          $customer_price = $value3['customer_price'];
                          $actual_price = @$value3['actual_price'];
                          $difference_price = @$value3['difference_price'];
                          $dealer_price = $value3['dealer_price'];
                          $powertech_share = 0;
                          $incentive = $value3['incentive'];
                        } else {
                          $customer_price = 0;
                          $actual_price = 0;
                          $difference_price = 0;
                          $dealer_price = 0;
                          $powertech_share = 0;
                          $incentive = 0;
                        }
                        $total_cp= $total_cp+$customer_price;
                        $total_ap= $total_ap+$actual_price;
                        $total_diff= $total_diff+$difference_price;
                        $total_dp= $total_dp+$dealer_price;
                        $total_pt_share= $total_pt_share+$powertech_share;
                        $total_int= $total_int+$incentive;
                      
                      }  ?>
                    <tr>
                      <th style="text-align:center;">
                        @if(count($result3)>0)
                          <button id="download3" class="btn btn-success" value="asm_wise">Download</button>
                        @endif
                      </th>
                      <th colspan="8" style="font-size: 12px;">Total Record: {{count($result3)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_cp)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_ap)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_diff)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_dp)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_pt_share)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_int)}}</th>
                      <th></th>
                    </tr>
                    <tr>
                      <th>Date</th>
                      <th>Dealer Name</th>
                      <th>Job Card</th>
                      <th>Bill No.</th>
                      <th>Regn No.</th>
                      <th>Advisor</th>
                      <th>Model</th>
                      <th>Treatment</th>
                      <th>Labour Code</th>
                      <th style="background-color: #FFFF00;">Customer Price</th>
                      <th style="background-color: #FFFF00;">Actual Price</th>
                      <th style="background-color: #FFFF00;">Difference</th>
                      <th style="background-color: #FFFF00;">Dealer Price</th>
                      <th style="background-color: #FFFF00;">PT Share</th>
                      <th style="background-color: #FFFF00;">Incentive</th>
                      <th>Remark</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if(count($result3)>0){               
                      foreach($result3 as $val3) {
                    ?>
                        <tr>
                          <td style="width: 100px;">{{date("d-M-Y", strtotime($val3['job_date']))}}</td>
                          <td>{{get_dealer_name($val3['dealer_id'])}}</td>
                          <td>{{$val3['job_card_no']}}</td>
                          <td>{{$val3['bill_no']}}</td>
                          <td>{{strtoupper($val3['regn_no'])}}</td>
                          <td>{{get_advisor_name($val3['advisor_id'])}}</td>
                          <td>{{get_model_name($val3['model_id'])}}</td>
                          <td>{{$val3['treatment_name']}}</td>
                          <td>{{$val3['labour_code']}}</td>
                          <td>{{round($val3['customer_price'])}}</td>
                          <td>{{round(@$val3['actual_price'])}}</td>
                          <td>{{round(@$val3['difference_price'])}}</td>
                          <td>{{round($val3['dealer_price'])}}</td>
                          <td>{{round($powertech_share)}}</td>
                          <td>{{round($val3['incentive'])}}</td>
                          <td>{{$val3['remarks']}}</td>
                        </tr>                            
                    <?php
                      $i++;
                      } 
                    }else{?>
                      <tr>
                        <td colspan="16">No Record</td>                          
                      </tr>
                    <?php }?>
                  </tbody>
                  <tfoot>
                    
                  </tfoot>
                </table>
              @endif
              @if(Session::get('oldReport')=='dealer')
                <table class="table table-bordered table-striped report-table">
                  <thead>
                  <?php 
                  $i=1;  
                    $total_cp=$total_ap=$total_diff=$total_dp=$total_pt_share=$total_int=0;
                    foreach($result as $value1) {
                        if (@$value1['job_type'] == '5') {
                          $customer_price = $value1['customer_price'];
                          $actual_price = @$value1['actual_price'];
                          $difference_price = @$value1['difference_price'];
                          $dealer_price = $value1['dealer_price'];
                          $powertech_share = 0;
                          $incentive = $value1['incentive'];
                        } else {
                          $customer_price = 0;
                          $actual_price = 0;
                          $difference_price = 0;
                          $dealer_price = 0;
                          $powertech_share = 0;
                          $incentive = 0;
                        }
                        $total_cp= $total_cp+$customer_price;
                        $total_ap= $total_ap+$actual_price;
                        $total_diff= $total_diff+$difference_price;
                        $total_dp= $total_dp+$dealer_price;
                        $total_pt_share= $total_pt_share+$powertech_share;
                        $total_int= $total_int+$incentive;
                      
                      }
                      ?>
                    <tr>

                      <th style="text-align:center;">
                        @if(count($result)>0)
                          <button id="download" class="btn btn-success" value="dealer_wise">Download</button>
                        @endif
                      </th>
                      <th colspan="7" style="font-size: 12px;">Total Record: {{count($result)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_cp)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_ap)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_diff)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_dp)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_pt_share)}}</th>
                      <th style="background-color: #EEECE1;">{{round($total_int)}}</th>
                      <th></th>
                    </tr>
                    <tr>
                      <th>Date</th>
                      <th>Job Card</th>
                      <th>Bill No.</th>
                      <th>Regn No.</th>
                      <th>Advisor</th>
                      <th>Model</th>
                      <th>Treatment</th>
                      <th>Labour Code</th>
                      <th style="background-color: #FFFF00;">Customer Price</th>
                      <th style="background-color: #FFFF00;">Actual Price</th>
                      <th style="background-color: #FFFF00;">Difference</th>
                      <th style="background-color: #FFFF00;">Dealer Price</th>
                      <th style="background-color: #FFFF00;">PT Share</th>
                      <th style="background-color: #FFFF00;">Incentive</th>
                      <th>Remark</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if(count($result)>0){               
                      foreach($result as $value) {
                    ?>
                        <tr>
                          <td style="width: 100px;">{{date("d-M-Y", strtotime($value['job_date']))}}</td>
                          <td>{{$value['job_card_no']}}</td>
                          <td>{{$value['bill_no']}}</td>
                          <td>{{strtoupper($value['regn_no'])}}</td>
                          <td>{{get_advisor_name($value['advisor_id'])}}</td>
                          <td>{{get_model_name($value['model_id'])}}</td>
                          <td>{{$value['treatment_name']}}</td>
                          <td>{{$value['labour_code']}}</td>
                          <td>{{round($value['customer_price'])}}</td>
                          <td>{{round(@$value['actual_price'])}}</td>
                          <td>{{round(@$value['difference_price'])}}</td>
                          <td>{{round($value['dealer_price'])}}</td>
                          <td>{{round($powertech_share)}}</td>
                          <td>{{round($value['incentive'])}}</td>
                          <td>{{$value['remarks']}}</td>
                        </tr>                            
                    <?php
                      $i++;
                      } 
                    }else{?>
                      <tr>
                        <td colspan="15">No Record</td>                          
                      </tr>
                    <?php }?>
                  </tbody>
                  <tfoot>
                    
                  </tfoot>
                </table>
              @endif
              @if(Session::get('oldReport')=='advisor')
                <div class="col-md-3 table-responsive">
                  <table style="width: 100%; border: 1px solid #ccc !important; background-color: #fff;" class="table-bordered calendar-table">
                    <tr>
                      <th colspan="2" style="text-align: center;">Monthly Treatments till Date</th>
                    </tr>
                    <tr>
                      <td>RO:</td>
                      <td>{{number_format(@$total_job_array['mtd_total'])}}</td>
                    </tr>
                    <tr>
                      <th colspan="2">VAS</th>
                    </tr>
                    <tr>
                      <td>No of Trmt:</td>
                      <td>{{number_format(@$total_job_array['mtd_vas_total'])}}</td>
                    </tr>
                    <tr>
                      <td>Amount:</td>
                      <!-- <td>{{number_format(@$total_job_array['mtd_vas_value'])}}</td> -->
                      <td>{{number_format(@$total_job_array['mtd_actual_value'])}}</td>
                    </tr>
                    <tr>
                      <td>Value Per Treatment</td>
                      <th>{{vas_in_percentage(@$total_job_array['mtd_actual_value'],@$total_job_array['mtd_vas_total'])}}</th>
                    </tr>
                    <tr>
                      <th colspan="2">HVT</th>
                    </tr>
                    <tr>
                      <td>No of Trmt:</td>
                      <td>{{number_format(@$total_job_array['mtd_hvt_total'])}}</td>
                    </tr>
                    <tr>
                      <td>Amount:</td>
                      <td>{{number_format(@$total_job_array['mtd_hvt_value'])}}</td>
                    </tr>
                    <tr>
                      <td>HVT %</td>
                      <th>{{hvt_in_percentage(@$total_job_array['mtd_hvt_value'],@$total_job_array['mtd_vas_value'])}}%</th>
                    </tr>
                  </table>
                </div>
                <div class="col-md-9">
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped report-table">
                      <thead>
                        <tr>
                          <th>Advisor Name</th>
                          <th style="text-align: center;" colspan="5">MTD</th>
                          <th>
                            @if(count($advisors)>0)
                            <button class="btn btn-success" id="download1">Download</button>
                            @endif
                          </th>
                        </tr>
                        <tr>
                          <th style="font-size: 12px;">Total Records: {{count($advisors)}}</th>
                          <th style="text-align: center;" colspan="3">VAS</th>
                          <th style="text-align: center;" colspan="3">HVT</th>
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
                        <?php if(count($advisors)>0){ ?>
                        @foreach($advisors as $val)
                          <tr>
                            <td>{{get_advisor_name($val['advisor_id'])}}</td>
                            <td>{{round($val['vas_customer_price'])}}</td>
                            <td>{{round(@$val['vas_actual_price'])}}</td>
                            <td>{{round($val['vas_incentive'])}}</td>
                            <td>{{round($val['hvt_customer_price'])}}</td>
                            <td>{{round(@$val['hvt_actual_price'])}}</td>
                            <td>{{round($val['hvt_incentive'])}}</td>
                          </tr>
                        @endforeach
                        <?php }else{ ?>
                          <tr>
                            <td colspan="7">No Record</td>                          
                          </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              @endif
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