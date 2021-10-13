@extends('layouts.dashboard')
{{-- {{dd("edit blade")}} --}}
@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Attendance Shifting
            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Attendance Shifting</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Attendance Shifting</h3>
                  <!-- <a href="{{url('/admin/addContact')}}/" class="btn btn-success floatright">Add Contact</a> -->
                </div><!-- /.box-header -->
                <!-- <form action="" method="GET"> 
                  <div class="row">
                    <div class="col-xs-12 col-md-12">
                      <div class="col-xs-6 col-md-6 pull-left">
                      </div>
                      <div class="col-xs-4 col-md-4 pull-right">
                        <div class="input-group ">
                          <input type="text" class="form-control" name="search" placeholder="Search by name, or mobile no. or Designation" id="txtSearch">
                          <div class="input-group-btn">
                            <button class="btn btn-primary" type="submit">
                              <span class="glyphicon glyphicon-search"></span>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </form> -->
                <div class="box-body">
                  @if(Session::has('error'))
                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                  @endif
                  @if(Session::has('success'))
                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                  @endif
                  <table id="exa" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Sr. No.</th>
                        <th>ASM</th>
                        <th>Sales Executive</th>
                        <th>Dealer Name</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($result)>=1){
                        $i=1;
                        $s = $result->perPage() * ($result->currentPage() - 1);
                        foreach($result as $value) { 
                      ?>
                      <tr>
                        <td>{{++$s}}</td>
                        <td>{{get_name(@$value->reporting_authority)}}</td>
                        <td>{{get_name(@$value->user_id)}}</td>
                        <td>{{get_name(@$value->dealer_id)}}</td>
                        <td>{{$value->from_date}}</td>
                        <td>{{$value->to_date}}</td>
                        <td>@if($value->status == "1") 
                            {{'Activate'}}
                            @elseif($value->status == "2") 
                            {{'Shifted'}}
                            @elseif($value->status == "3") 
                            {{'Changed'}} 
                            @else 
                            {{'Deactivate'}} 
                            @endif
                        </td>
                        <td>
                          <a href="{{ url('/admin/editEmpHierarchy/')}}/{{$value->user_id}}" class="btn btn-success">Edit</a>
                          {{-- <a href="{{ url('/admin/editEmpHierarchy/')}}/" class="btn btn-success" @if($value->status == 3){{'disabled'}} @endif>Edit</a> --}}
                          {{-- <a href="{{ url('/admin/statusEmpHierarchy/delete/')}}/" onclick="return confirm('Are you sure want to delete?')" class="btn btn-danger">Delete</a> --}}
                        </td>
                      </tr>   
                      <?php
                         $i++;
                         }
                        }else{?>
                      <tr>
                        <td colspan="5">No Record</td>                          
                      </tr>
                     <?php  }?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr. No.</th>
                        <th>ASM</th>
                        <th>Sales Executive</th>
                        <th>Dealer Name</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Status</th>
                        <th>Action</th>
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