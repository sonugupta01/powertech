@extends('layouts.dashboard')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Staff Management
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/asm')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Staff Management</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="box">
          <div class="box-header">
            <div class="row">
              <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="col-xs-12 col-sm-6 col-md-2 pull-left">
                  <h3 class="box-title">Staff Management List</h3>
                </div>
                <form id="form1" action="" method="GET">
                  <div class="col-xs-12 col-sm-6 col-md-2">
                    <div class="input-group form-group">
                      <select class="form-control" id="firm_id" name="firm_id">
                        <option value="">Select Firm</option>
                        @foreach($firms as $firm)
                          <option value="{{$firm->id}}" {{($firm_id==$firm->id)?'selected':''}}>{{$firm->short_code}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-xs-12 col-sm-6 col-md-2">
                    <div class="input-group form-group">
                      <select class="form-control" id="dealer_id" name="dealer_id">
                        <option style="width: 350px;" value="">Select Dealer</option>
                        @foreach($dealers as $dealer)
                          <option value="{{$dealer->id}}" {{($dealer_id==$dealer->id)?'selected':''}}>{{$dealer->name}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-xs-12 col-sm-6 col-md-2">
                    <div class="input-group form-group">
                      <select class="form-control" id="office_id" name="office_id">
                        <option style="width: 350px;" value="">Select Office</option>
                        @foreach($offices as $office)
                          <option value="{{$office->id}}" {{($office_id==$office->id)?'selected':''}}>{{$office->name}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-xs-12 col-sm-6 col-md-2">
                    <div class="input-group form-group">
                      <select class="form-control" id="designation_id" name="designation_id" >
                        <option value="">Select Designation</option>
                        @foreach($designations as $designation)
                          <option {{($des==$designation->id)?'selected':''}} value="{{$designation->id}}">{{$designation->designation}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-xs-12 col-sm-6 col-md-2">
                    <div class="input-group from-group pull-right">
                      <select class="form-control" id="status" name="status">
                        <option value="">Status</option>
                        <option value="activated" {{$status=='activated'?'selected':''}}>Activated</option>
                        <option value="deactivated" {{$status=='deactivated'? 'selected':''}}>Deactivated</option>
                      </select>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div><!-- /.box-header -->
          <form action="" method="GET"> 
            <div class="row">
              <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="col-xs-12 col-sm-6 col-md-6 pull-left">
                  <b style="margin-top: 10px; font-size: 12px; display: block;">Total Records: {{$result->total()}}</b>
                </div>
                <!-- <div class="col-md-3">
                      <form method="GET" action="{{asset('/rsm/downloadStaff')}}">
                        <input type="hidden" name="firm_id" value="{{@$firm_id}}">
                        <input type="hidden" name="dealer_id" value="{{@$dealer_id}}">
                        <input type="hidden" name="designation_id" value="{{@$des}}">
                        <input type="submit" class="btn btn-success floatright btn-div" name="download" id="download" value="Download">
                      </form>
                       <a href="{{url('/rsm/addStaffMember')}}" class="btn btn-success floatright btn-div"><i class="fa fa-download" style="font-size:24px"></i></a>
                    </div> -->
                <div class="col-xs-12 col-sm-6 col-md-4 pull-right">
                  <div class="input-group form-group">
                    <input type="text" class="form-control" name="search" placeholder="Search by name or mobile no." id="txtSearch">
                    <div class="input-group-btn">
                      <button class="btn btn-primary" type="submit" style="height: 34px;">
                        <span class="glyphicon glyphicon-search"></span>
                      </button>
                    </div>
                  </div>
                </div> 
              </div>
            </div>
          </form>

          <div class="box-body table-responsive">
            @if(Session::has('error'))
            <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <table id="exa" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Action</th>
                  <th>Firm Code</th>
                  <th>Emp. Code</th>
                  <th>Name</th>
                  <th>Mobile No.</th>
                  <th>Alt. Mobile</th>
                  <th>Salary</th>
                  <th>Dealer Name</th>
                  <th>Dept.</th>
                  <th>Dsgn.</th>
                  <th>Access Rights</th>
                  <th>D.O.J</th>
                  <th>D.O.L</th>
                  <!-- <th>View</th> -->
                  <!-- <th>Status</th> -->
                </tr>
              </thead>
              <tbody>
                <?php if(count($result)>=1){
                  $i=1;
                  foreach($result as $value) { 
                    ?>
                    <tr>
                    <td class="text-center">
                        {{-- @if($value->status == "0")
                        <a href="{{ url('/asm/editStaffMember/')}}/{{$value->user_id}}" class="btn btn-info" disabled>Edit</a>
                        <a href="{{url('asm/statusStaff/activate')}}/{{$value->user_id}}" class="btn btn-success" onclick="return confirm('Are you sure want to Activate?')">Activate</a>
                        <a><i class="fa fa-calendar" style="font-size: 21px; opacity: 0.3; color: #0099CC" title="Attendance Shifting"></i></a>
                        @else
                        <a href="{{ url('/asm/editStaffMember/')}}/{{$value->user_id}}" class="btn btn-info">Edit</a>
                        <a href="{{url('asm/statusStaff/deactivate')}}/{{$value->user_id}}" class="btn btn-danger" onclick="return confirm('Are you sure want to Deactivate?')">Deactivate</a>
                        <a href="{{ url('/asm/editEmpHierarchy/')}}/{{$value->user_id}}" class="btn btn-success">Attendace Shift</a>
                        @endif --}}
                        @if(!empty($value->dealer_id))
                        <a href="{{ url('/asm/editEmpHierarchy/')}}/{{$value->user_id}}"><i class="fa fa-calendar" style="font-size: 25px; color: #0099CC" title="Attendance Shifting"></i></a>
                        @else
                        <a><i class="fa fa-calendar" style="font-size: 25px; opacity: 0.3; color: #0099cc;"></i></a>
                        @endif
                        {{-- <a href="{{ url('/asm/statusStaff/activate/')}}/{{$value->user_id}}" onclick="return confirm('Are you sure want to delete?')" class="btn btn-danger">Delete</a> --}}
                        
                      </td>
                      @if(!empty($value->firm_id))
                      <td>{{get_firm_short_code(@$value->firm_id)}}</td> 
                      @else
                      <td> {{'-'}}</td>
                      @endif                     
                      <td>{{$value->emp_code}}</td>                            
                      <td>{{ucwords($value->name)}}</td>
                      <td>{{$value->mobile_no}}</td>
                      <td>{{$value->alt_mobile_no}}</td>
                      <td>{{@getsalarybyid($value->user_id)}}</td>
                      @if(!empty($value->dealer_id))
                      <td>{{get_dealer_name($value->dealer_id)}}</td>
                      @else
                      <td>{{'-'}}</td>
                      @endif
                      @if(!empty($value->reporting_authority))
                      @else
                      <td>{{'-'}}</td>
                      @endif
                      <td>{{get_department_name($value->department_id)}}</td>
                      <td>{{get_designation_name($value->designation_id)}}</td>
                      {{-- <td><a href="{{ url('/asm/viewServices/')}}/{{$value->user_id}}" class="btn btn-info">Services</a></td> --}}
                      <td>@if($value->role == "5") ASM @elseif($value->role == "3") All @else Attendance Only @endif</td>
                      <td>{{$value->doj}}</td>
                      @if(!empty($value->dol))
                      <td>{{$value->dol}}</td>
                      @else
                      <td>{{'Working'}}</td>
                      @endif
                      <!-- <td>@if($value->status == "1") Activate @else Deactivate @endif</td> -->
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
                <tr>
                  <th>Action</th>
                  <th>Firm Code</th>
                  <th>Emp. Code</th>
                  <th>Name</th>
                  <th>Mobile No.</th>
                  <th>Alt. Mobile</th>
                  <th>Salary</th>
                  <th>Dealer Name</th>
                  <th>Dept.</th>
                  <th>Dsgn.</th>
                  <th>Access Rights</th>
                  <th>D.O.J</th>
                  <th>D.O.L</th>
                  <!-- <th>View</th> -->
                  <!-- <th>Status</th> -->
                </tr>
              </tfoot>
            </table>
            <?php echo $result->links(); ?>
          </div><!-- /.box-body -->
        </div><!-- /.box -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->  
<script>
setTimeout(function() {
  $('.alert').fadeOut('slow');
}, 3000);

$('#firm_id').on('change',function(){
  $( "#form1" ).submit();
});
$('#asm_id').on('change',function(){
  $( "#form1" ).submit();
});
$('#dealer_id').on('change',function(){
  $( "#form1" ).submit();
});
$('#office_id').on('change',function(){
  $( "#form1" ).submit();
});
$('#designation_id').on('change',function(){
  $( "#form1" ).submit();
});
$('#status').on('change',function(){
  $( "#form1" ).submit();
});
</script> 
@endsection