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
                  <a href="#tab01" data-toggle="tooltip" title="Daily Report">Daily Report</a>
                </li>
                <li class="">
                  <a href="#tab02" data-toggle="tooltip" title="MIS">MIS</a>
                </li>
                <li class="">
                  <a href="#tab03" data-toggle="tooltip" title="DCF">DCF</a>
                </li>
              </ul>
            </div>
            <div class="tab-select-outer">
              <select id="tab-select">
                <option value="#tab01" {{ empty($tabName) || $tabName == 'tab01' ? 'selected' : '' }}>Daily Report</option>
                <option value="#tab02" {{ empty($tabName) || $tabName == 'tab02' ? 'selected' : '' }}>MIS</option>
                <option value="#tab03" {{ empty($tabName) || $tabName == 'tab03' ? 'selected' : '' }}>DCF</option>
              </select>
            </div>
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header" style="text-align: center; margin: 0 auto;">
                  <h3 class="box-title">Reports List</h3>
                </div><!-- /.box-header -->
                <div id="tab01" class="tab-contents">
                  <form action="" class="" method="GET"> 
                    <div class="row">
                      <div class="col-xs-12 col-md-offset-2 col-md-8">
                        <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                          <label>OEM</label>
                          <select name="oem" class="form-control">
                            <option value="">Select OEM</option>
                            @foreach($oems as $val)
                              <option value="{{$val->id}}" @if(@$oldOem == $val->id) {{'selected'}} @endif>{{$val->oem}}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                          <label>Group</label>
                          <select name="group" class="form-control">
                            <option value="">Select Group</option>
                            @foreach($groups as $val)
                              <option value="{{$val->id}}" @if(@$oldGroup == $val->id) {{'selected'}} @endif>{{$val->group_name}}</option>
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
                            <label>Report Type</label>
                            <div class="form-control required">
                              <input type="radio" {{(@$oldReport=='dealer')?'checked':''}} value="dealer" name="report_type" checked> Dealer Wise
                              <input type="radio" {{(@$oldReport=='advisor')?'checked':''}} value="advisor" name="report_type"> Advisor Wise
                            </div>
                          </div>
                            <div class="form-group report-field col-md-12 col-sm-12 col-xs-12" style="text-align: center;">
                              <input type="hidden" value="tab01" name="tabName">
                              <input class="btn btn-primary" type="submit" value="Submit">   
                            </div>
                      </div>
                    </div>
                  </form>
                  <button class="btn btn-success" id="all_dealers" style="margin-left: 48px;">Download Whole Report</button>
                  <div class="box-body table-responsive" style="overflow: auto;">
                      @if(Session::get('oldReport')=='dealer')
                      <table class="table table-bordered table-striped report-table">
                        <thead>
                         <?php 
                         $i=1;  
                            $total_cp=$total_dp=$total_int=0; 
                          foreach($result as $value1) {
                              $total_cp= $total_cp+$value1['customer_price'];
                              $total_dp= $total_dp+$value1['dealer_price'];
                              $total_int= $total_int+$value1['incentive'];
                            
                            }  ?>
                          <tr>
                            <th colspan="2" style="font-size: 12px;">Total Record: {{count($result)}}</th>
                            <th colspan="6"></th>
                            <th style="background-color: #EEECE1;">{{round($total_cp)}}</th>
                            <th style="background-color: #EEECE1;">{{round($total_dp)}}</th>
                            <th style="background-color: #EEECE1;">{{round($total_int)}}</th>
                            <th style="text-align:center;">
                                  @if(count($result)>0)
                                  <button id="download" class="btn btn-success" value="dealer_wise">Download</button>
                                @endif</th>
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
                            <th style="background-color: #FFFF00;">Dealer Price</th>
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
                                <td>{{round($value['dealer_price'])}}</td>
                                <td>{{round($value['incentive'])}}</td>
                                <td>{{$value['remarks']}}</td>
                              </tr>                            
                          <?php
                            $i++;
                            } 
                           }else{?>
                            <tr>
                              <td colspan="12">No Record</td>                          
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
                                <td>{{number_format(@$total_job_array['mtd_vas_value'])}}</td>
                              </tr>
                              <tr>
                                <td>Value Per Treatment</td>
                                <th>{{vas_in_percentage(@$total_job_array['mtd_vas_value'],@$total_job_array['mtd_vas_total'])}}</th>
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
                                <th style="text-align: center;" colspan="3">MTD</th>
                                <th>
                                  @if(count($advisors)>0)
                                  <button class="btn btn-success" id="download1">Download</button>
                                  @endif
                                </th>
                              </tr>
                              <tr>
                                <th style="font-size: 12px;">Total Records: {{count($advisors)}}</th>
                                <th style="text-align: center;" colspan="2">VAS</th>
                                <th style="text-align: center;" colspan="2">HVT</th>
                              </tr>
                              <tr>
                                <td></td>
                                <th>Cust Billing</th>
                                <th>Incentive</th>
                                <th>Cust Billing</th>
                                <th>Incentive</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php if(count($advisors)>0){ ?>
                              @foreach($advisors as $val)
                                <tr>
                                  <td>{{get_advisor_name($val['advisor_id'])}}</td>
                                  <td>{{round($val['vas_customer_price'])}}</td>
                                  <td>{{round($val['vas_incentive'])}}</td>
                                  <td>{{round($val['hvt_customer_price'])}}</td>
                                  <td>{{round($val['hvt_incentive'])}}</td>
                                </tr>
                              @endforeach
                              <?php }else{ ?>
                                <tr>
                                  <td colspan="5">No Record</td>                          
                                </tr>
                              <?php } ?>
                            </tbody>
                          </table>
                        </div>
                      </div>
                      @endif
                  </div>
                </div>
              <div class="tab-contents" id="tab02">
                <div class="box-body ">
                  
                  <form action="" class="" method="GET"> 
                    <div class="row">
                      <div class="col-xs-12 col-md-offset-2 col-md-8">
                        <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                          <label>OEM</label>
                          <select name="oem" class="form-control">
                            <option value="">Select OEM</option>
                            @foreach($oems as $val)
                              <option value="{{$val->id}}" @if(@$oldOem == $val->id) {{'selected'}} @endif>{{$val->oem}}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                          <label>Group</label>
                          <select name="group" class="form-control">
                            <option value="">Select Group</option>
                            @foreach($groups as $val)
                              <option value="{{$val->id}}" @if(@$oldGroup == $val->id) {{'selected'}} @endif>{{$val->group_name}}</option>
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
                            <input type="text"  id="selectMonth" name="month" value="{{@$oldMonth}}" placeholder="Select Month" value="" class="datePicker1 form-control" autocomplete="off" />
                            <input type="hidden" value="tab02" name="tabName">
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
                          <th colspan="6" style="text-align:center;">MIS</th>
                          <th style="text-align: center;">
                            <button id="download2" class="btn btn-success">Download</button>
                          </th>
                        </tr>
                        <tr>
                          <th>CDC</th>
                          <th>Cust Bill</th>
                          <th>Vendor</th>
                          <th>Incentive</th>
                          <th>MTD HVT</th>
                          <th>HVT Value</th>
                          <th>HVT %</th>
                          <th>RO</th>
                        </tr>
                        <?php $cp=$dp=$in=$hvt=$mtd_hvt=$service=0;
                         foreach ($mis as $val) {
                            $cp=$cp+round($val['customer_price']);
                            $dp=$dp+round($val['dealer_price']);
                            $in=$in+round($val['incentive']);
                            $hvt=$hvt+round($val['hvt_total']);
                            $mtd_hvt=$mtd_hvt+round($val['mtd_hvt_value']);
                            $service=$service+round($val['service_load']);
                        }   
                         ?>
                         <tr>
                          <th>Business Total</th>
                          <th>{{$cp}}</th>
                          <th>{{$dp}}</th>
                          <th>{{$in}}</th>
                          <th>{{$hvt}}</th>
                          <th>{{$mtd_hvt}}</th>
                          <th>{{hvt_in_percentage($mtd_hvt,$cp)}}%</th>
                          <th>{{$service}}</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if(count($mis)>0){ ?>
                        @foreach($mis as $val)
                        <tr>
                          <td style="background-color:#FFFF00; width: 275px;">{{get_name($val['dealer_id'])}}</td>
                          <td style="background-color:#B6DDE8;">{{round($val['customer_price'])}}</td>
                          <td style="background-color:#F7FED0;">{{round($val['dealer_price'])}}</td>
                          <td style="background-color:#FFFF00;">{{round($val['incentive'])}}</td>
                          <td style="background-color:#F2DDDC;">{{round($val['hvt_total'])}}</td>
                          <td style="background-color:#F2DDDC;">{{round($val['mtd_hvt_value'])}}</td>
                          <td style="background-color:#FFFF00;">{{hvt_in_percentage($val['mtd_hvt_value'],$val['customer_price'])}}%</td>
                          <td style="background-color:#B6DDE8;">{{$val['service_load']}}</td>
                        </tr>
                        @endforeach
                      <?php }else{ ?>
                        <tr>
                          <td colspan="8">No Record</td>                          
                        </tr>
                      <?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div><!-- /.box-body -->
              </div>
              <div class="tab-contents" id="tab03">
                <form action="" class="" method="GET"> 
                  <div class="row">
                    <div class="col-xs-12 col-md-offset-2 col-md-8">
                        <div class="input-group form-group report-field col-md-6 col-sm-6 col-xs-12" style="float: left !important;">
                          <label>Select Month</label>
                          <input type="text"  id="selectMonth" name="selectMonth" placeholder="Select Month" value="" class="datePicker1 form-control" autocomplete="off" />
                          <input type="hidden" value="tab03" name="tabName">
                        </div>
                        
                          <div class="form-group report-field col-md-6 col-sm-6 col-xs-12" style="margin-top:25px;">
                            <input class="btn btn-success" type="submit" value="Download">
                          </div>
                    </div>
                  </div>
                </form>
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
        <input type="hidden" name="month3" value="{{@$oldSelectMonth}}">
        <input type="hidden" name="report" value="dealer">
        <input type="hidden" name="download" value="dealer_wise">
      </form> 
      <form method="get" id="form22" action="{{url('/admin/downloadReport')}}">
        <input type="hidden" name="from2" value="{{@$oldFromDate}}">
        <input type="hidden" name="to2" value="{{@$oldToDate}}">
        <input type="hidden" name="dealer2" value="{{@$oldDealer}}">
        <input type="hidden" name="report" value="advisor">
        <input type="hidden" name="month4" value="{{@$oldSelectMonth}}">
        <input type="hidden" name="download" value="advisor_wise">
      </form>  
      <form method="get" id="form32" action="{{url('/admin/downloadMIS')}}">
        <input type="hidden" name="from12" value="{{@$oldFromDate1}}">
        <input type="hidden" name="to12" value="{{@$oldToDate1}}">
        <input type="hidden" name="selectMonth2" value="{{@$oldMonth}}">
      </form>  
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
  $(document).on('click','#download2',function(){
    $('#form32').submit();
  });
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

   var tab="<?php echo $tabName; ?>";
     $('#tab-button li a[href="#'+tab+'"]').click();

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

  $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
  });
 
</script> 
@endsection