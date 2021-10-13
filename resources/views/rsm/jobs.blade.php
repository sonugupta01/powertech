@extends('layouts.dashboard')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Jobs
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/rsm')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Jobs</li>
    </ol>
  </section>
  <div class="modal fade" id="myModal" role="dialog" style="z-index: inherit;">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Add RO</h4>
        </div>
        <div class="modal-body">
          <div class=" ro-box box box-primary" style=" margin-top: 10px;">
            <div class="box-body">
              <form method="POST" action="{{url('/rsm/addServiceLoad')}}">
                <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                <div class="form-group">
                  <label for="dealer_id">Dealer<span class="required-title">*</span></label>
                  <select class="form-control required" id="dealer_id" name="dealer_id">
                    @foreach($dealers as $dealer)
                      <option value="{{$dealer}}">{{ucwords(get_dealer_name($dealer))}}</option>
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
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Jobs List</h3>
            <button type="button" class="btn btn-info floatright" data-toggle="modal" data-target="#myModal" style="margin-left: 20px;">Add RO</button>
            <!-- Modal -->
            <a href="{{url('/rsm/addJob')}}" class="btn btn-success floatright">Add Job</a>
          </div><!-- /.box-header -->
          <form action="" method="GET" id="form1"> 
            <div class="row">
              <div class="col-xs-12 col-md-12">
                <div class="col-xs-12 col-sm-6 col-md-7 pull-left">
                  <div class="col-xs-4 col-sm-6 col-md-5">
                    <div class="input-group from-group">
                      <input type="text" class="form-control" name="regn_no" value="{{@$regn_no}}" placeholder="Search by Regn No.">
                    </div>
                  </div>
                  <div class="col-xs-4 col-sm-6 col-md-4">
                    <div class="input-group from-group">
                        <input type="submit" class="btn btn-primary btn-div" value="Search">
                    </div>
                  </div>
                  <!-- <div class="col-xs-4 col-sm-6 col-md-3">
                    <div class="input-group from-group">
                      <select class="form-control job_type" name="job_type" id="job_type">
                        <option value="">All</option>
                        <option value="1" @if(@$job_type == 1){{'Selected'}}@endif>FOC</option>
                        <option value="2"  @if(@$job_type == 2){{'Selected'}}@endif>Demo</option>
                        <option value="3"  @if(@$job_type == 3){{'Selected'}}@endif>Recheck</option>
                        <option value="4"  @if(@$job_type == 4){{'Selected'}}@endif>Repeat Work</option>
                        <option value="5"  @if(@$job_type == 5){{'Selected'}}@endif>Paid</option>
                      </select>
                    </div>
                  </div> -->
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4 pull-right">
                  <div class="input-group ">
                    <select class="form-control" id="supervisor" name="search">
                      <option style="width: 350px;" value="">Select Staff Member</option>
                      @foreach($supervisors as $value)
                        <option {{($oldSupervisor==$value->id)?'selected':''}} value="{{$value->id}}">{{ucwords($value->name)}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </form>
          <b style="margin-top: 10px; margin-left: 10px; font-size: 12px; display: block;">Total Records: {{$result->total()}}</b>
          <div class="box-body">
            @if(Session::has('error'))
              <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
              <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <div class="table-responsive">
              <table id="exa" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>
                      <button id='delete' class="btn btn-danger">Delete</button>
                    </th>
                    <th>Action</th>
                    <th>Dealer</th>
                    <th>Job Date</th>
                    <th>Job Card No.</th>
                    <th>Bill No.</th>
                    <th>Regn No.</th>
                    <th>Model</th>
                    <th>Advisor</th>
                    <th>Treatment</th>
                    <th>Customer Price</th>
                    <th>Dealer Price</th>
                    <th>Incentive</th>
                    <!-- <th>Options</th> -->
                    <th>Remark</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if(count($result)>=1){
                    $i=1;
                    $data_treatment='';
                     foreach($result as $value) { 
                        $treatment_data=array();
                        $data=array();
                        $data=json_decode($value->treatments);
                        if(@$data){
                          foreach($data as $val) {
                            $treatment_data[]=$val->treatment;
                          }
                        }
                  ?>
                      <tr>
                        <td><input type="checkbox" name="id" value="{{$value->id}}"></td>
                        <td>
                            <a href="{{ url('/rsm/editJob/')}}/{{$value->id}}"><i class="fa fa-edit" style="font-size: 20px;"></i></a>
                            <a href="{{ url('/rsm/statusJob/delete/')}}/{{$value->id}}" onclick="return confirm('Are you sure want to delete?')"><i class="fa fa-trash" style="color: #d73925; font-size: 20px;"></i></a>
                        </td>
                        <td>{{get_dealer_name($value->dealer_id)}}</td>
                        <td>{{date('d-M-Y',strtotime($value->job_date))}}</td>
                        <td>{{$value->job_card_no}}</td>
                        <td>{{$value->bill_no}}</td>
                        <td>{{$value->regn_no}}</td>
                        <td>{{get_model_name($value->model_id)}}</td>
                        <td>{{get_advisor_name($value->advisor_id)}}</td>
                        <td>{{implode(', ',$treatment_data)}}</td>                           
                        <td>{{round($value->customer_price)}}</td>
                        <td>{{round($value->dealer_price)}}</td>
                        <td>{{round($value->incentive)}}</td>
                        <!-- <td>
                          @if($value->foc_options == 1){{'FOC'}} @elseif($value->foc_options == 2){{'Demo'}} @elseif($value->foc_options == 3) {{'Recheck'}} @elseif($value->foc_options == 4){{'Repeat Work'}} 
                          @else{{'Paid'}}
                          @endif
                        </td> -->
                        <td>{{$value->remarks}}</td>
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
                  <tr>
                    <th></th>
                    <th>Action</th>
                    <th>Dealer</th>
                    <th>Job Date</th>
                    <th>Job Card No.</th>
                    <th>Bill No.</th>
                    <th>Regn No.</th>
                    <th>Model</th>
                    <th>Advisor</th>
                    <th>Treatment</th>                       
                    <th>Customer Price</th>
                    <th>Dealer Price</th>
                    <th>Incentive</th>
                    <!-- <th>Options</th> -->
                    <th>Remark</th>
                  </tr>
                </tfoot>
              </table>
            </div>
            <?php echo $result->links(); ?>
          </div><!-- /.box-body -->
        </div><!-- /.box -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<form id="deleteForm" action="{{url('/rsm/deleteJobs')}}" method="GET">
  <input type="hidden" class="" name="selectedId" value="">
</form> 
<script type="text/javascript">
setTimeout(function() {
  $('.alert').fadeOut('slow');
}, 3000);
  $(document).ready(function(){
   $('#supervisor').on('change',function(){
      $( "#form1" ).submit();
   });
   $('#job_type').on('change',function(){
      $( "#form1" ).submit();
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
    $("#delete").click(function(){
        var favorite = [];
        $.each($("input[name='id']:checked"), function(){            
            favorite.push($(this).val());
        });
        $("input[name='selectedId']").val(favorite);
        $( "#deleteForm" ).submit();
        //alert("My favourite sports are: " + favorite.join(", "));
    });
  });
</script>    
@endsection