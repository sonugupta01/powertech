@extends('layouts.dashboard')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Job Card Treatment List
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/rsm')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Job Card Treatment List</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Job Card Treatment List</h3>
          </div><!-- /.box-header -->
          <form action="" method="GET" id="form1"> 
            <div class="row">
              <div class="col-xs-12 col-md-12 col-sm-12">
                <div class="col-xs-12 col-sm-5 col-md-5">
                  <div class="input-group from-group">
                    <input type="text" class="form-control" name="search" placeholder="Search by Job card no, Bill no. or Registration no." id="txtSearch" value="{{ old('search', $search) }}">
                    <div class="input-group-btn">
                      <button class="btn btn-primary" type="submit" style="height: 34px;">
                        <span class="glyphicon glyphicon-search"></span>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-xs-12 col-sm-2 col-md-2">
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
                </div>
                <div class="col-xs-12 col-sm-5 col-md-5"></div>
              </div>
            </div>
          </form>
          <b style="margin-top: 10px; margin-left: 10px; font-size: 12px; display: block;">Total Records: </b>
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
                    {{-- <th>
                      <button id='delete' class="btn btn-danger">Delete</button>
                    </th> --}}
                    <th>Job Date</th>
                    <th>Dealer</th>
                    <th>Job Card No.</th>
                    <th>Bill No.</th>
                    <th>Regn No.</th>
                    <th>Treatment</th>
                    <th>Customer Price</th>
                    <th>Actual Price</th>
                    <th>Difference (+/-)</th>
                    <th>Job Type</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if(count($result)>=1){ 
                    $i=1;
                    foreach($result as $value) { 
                  ?>
                  <tr>
                    {{-- <td><input type="checkbox" name="id" value="{{$value->id}}"></td> --}}
                    <td>{{date('d-M-Y',strtotime($value->job_date))}}</td>
                    <td>{{get_dealer_name($value->dealer_id)}}</td>
                    <td>{{$value->job_card_no}}</td>
                    <td>{{$value->bill_no}}</td>
                    <td>{{$value->regn_no}}</td>
                    <td>{{$value->treatment}}</td>
                    <td>{{round($value->customer_price)}}</td>
                    <td>{{round(@$value->actualPrice)}}</td>
                    <td>{{round(@$value->difference)}}</td>
                    <td>
                      @if(@$value->job_type == 1){{'FOC'}} @elseif(@$value->job_type == 2){{'Demo'}} @elseif(@$value->job_type == 3) {{'Recheck'}} @elseif(@$value->job_type == 4){{'Repeat Work'}} 
                      @else{{'Paid'}}
                      @endif
                    </td>
                  </tr>
                  <?php 
                      $i++;
                    }
                  }else{?>
                  <tr>
                    <td colspan="9">No Record</td>                          
                  </tr>
                  <?php }?>
                </tbody>
                <tfoot>
                  <tr>
                    {{-- <th></th> --}}
                    <th>Job Date</th>
                    <th>Dealer</th>
                    <th>Job Card No.</th>
                    <th>Bill No.</th>
                    <th>Regn No.</th>
                    <th>Treatment</th>
                    <th>Customer Price</th>
                    <th>Actual Price</th>
                    <th>Difference (+/-)</th>
                    <th>Job Type</th>
                  </tr>
                </tfoot>
              </table>
            </div>
            {{-- {{ $result->links() }} --}}
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