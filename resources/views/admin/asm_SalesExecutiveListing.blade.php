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
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/asm')}}"><i class="fa fa-dashboard"></i>ASM Management</a></li>
      <li class="active">Sales Executives</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Sales Executive List Of <b>{{get_name($asm_id)}}</b></h3>
            <!-- <a href="{{url('/admin/addStaffMember')}}" class="btn btn-success floatright btn-div">Add Staff Member</a> -->
          </div><!-- /.box-header -->
          <form action="" method="GET"> 
            <div class="row">
              <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="col-xs-12 col-sm-6 col-md-6 pull-left">
                  <b style="margin-top: 10px; font-size: 12px; display: block;">Total Records: {{$result->total()}}</b>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4 pull-right">
                  <div class="input-group form-group">
                    <input type="text" class="form-control" name="search" placeholder="Search by name or mobile no." id="txtSearch">
                    <div class="input-group-btn">
                      <button class="btn btn-primary" type="submit">
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
                  <th>Emp. Code</th>
                  <th>Name</th>
                  <th>Mobile No.</th>
                  <th>Dealer Name</th>
                  <th>Department</th>
                  <th>Designation</th>
                  <th>Access Rights</th>
                  <th>D.O.J</th>
                  <th>Status</th>
                  <!-- <th>Action</th> -->
                </tr>
              </thead>
              <tbody>
                
                <?php if(count($result)>=1){
                  $i=1;
                  foreach($result as $value) { 
                    ?>
                    <tr>
                      <td>{{$value->emp_code}}</td>                            
                      <td>{{ucwords($value->name)}}</td>
                      <td>{{$value->mobile_no}}</td>
                      <td>{{get_dealer_name($value->dealer_id)}}</td>
                      <td>{{get_department_name($value->department_id)}}</td>
                      <td>{{get_designation_name($value->designation_id)}}</td>
                      <td>@if($value->role == "3") All @else Attendance Only @endif</td>
                      <td>{{$value->doj}}</td>
                      <td>@if($value->status == "1") Activate @else Deactivate @endif</td>
                      <!-- <td> -->
                        <!-- @if($value->status == "0")
                        <a href="{{ url('/admin/editStaffMember/')}}/{{$value->user_id}}" class="btn btn-success" disabled>Edit</a>
                        @else
                        <a href="{{ url('/admin/editStaffMember/')}}/{{$value->user_id}}" class="btn btn-success">Edit</a>
                        @endif -->
                        <!--<a href="{{ url('/admin/statusStaff/delete/')}}/{{$value->user_id}}" onclick="return confirm('Are you sure want to delete?')" class="btn btn-danger">Delete</a>-->
                      <!-- </td> -->
                    </tr>   
                    <?php
                    $i++;
                  }
                }else{?>
                <tr>
                  <td colspan="8">No Record</td>                          
                </tr>
                <?php }?>
              </tbody>
              <tfoot>
                <tr>
                  <th>Emp. Code</th>
                  <th>Name</th>
                  <th>Mobile No.</th>
                  <th>Dealer Name</th>
                  <th>Department</th>
                  <th>Designation</th>
                  <th>Access Rights</th>
                  <th>D.O.J</th>
                  <th>Status</th>
                  <!-- <th>Action</th> -->
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
</script> 
@endsection