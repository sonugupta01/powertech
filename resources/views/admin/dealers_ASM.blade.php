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
      <li><a href="{{url('/admin/dealer_management')}}"><i class="fa fa-dashboard"></i>Dealers Management</a></li>
      <li class="active">Dealer's Authority</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Authority Of Dealer <b>{{get_dealer_name($dealer_id)}}</b></h3>
            <!-- <a href="{{url('/admin/addStaffMember')}}" class="btn btn-success floatright btn-div">Add Staff Member</a> -->
          </div><!-- /.box-header -->

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
                  <!-- <th>Center Code</th> -->
                  <th>Name</th>
                  <th>Mobile No.</th>
                  <th>Email</th>
                  <th>Designation</th>
                  <th>Status</th>
                  <!-- <th>Action</th> -->
                </tr>
              </thead>
              <tbody>
                
                <?php if(count($authorities)>0){
                  $i=1;
                  foreach($authorities as $authority) { 
                ?>
                    <tr>
                      {{-- {{$asm_data->center_code}} --}}
                      <td>{{ucwords($authority->name)}}</td>
                      <td>{{$authority->mobile_no}}</td>
                      <td>{{$authority->email}}</td>
                      <td>{{get_designation_by_userid($authority->id)}}</td>
                      <td>@if($authority->status == "1") Activate @else Deactivate @endif</td>
                    </tr>   
                    
                <?php 
                  $i++;
                    }
                  } else { ?>
                <tr>
                  <td colspan="8">No Record</td>                          
                </tr>
                <?php }?>
              </tbody>
              <tfoot>
                <tr>
                  <!-- <th>Center Code</th> -->
                  <th>Name</th>
                  <th>Mobile No.</th>
                  <th>Email</th>
                  <th>Designation</th>
                  <th>Status</th>
                  <!-- <th>Action</th> -->
                </tr>
              </tfoot>
            </table>
            
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