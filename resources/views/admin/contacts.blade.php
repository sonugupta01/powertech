@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Contacts
            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('/admin/dealer_management')}}"><i class="fa fa-users"></i> Dealer Management <span class="active">({{get_name($dealer_id)}})</span></a></li>
            <li class="active">Contacts</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Contacts List</h3>
                  <a href="{{url('/admin/addContact')}}/{{$dealer_id}}" class="btn btn-success floatright">Add Contact</a>
                </div><!-- /.box-header -->
                <form action="" method="GET"> 
                  <div class="row">
                    <div class="col-xs-12 col-md-12">
                      <div class="col-xs-6 col-md-6 pull-left">
                      </div>
                      <div class="col-xs-4 col-md-4 pull-right">
                        <!-- <div class="input-group ">
                          <input type="text" class="form-control" name="search" placeholder="Search by name, or mobile no. or Designation" id="txtSearch">
                          <div class="input-group-btn">
                            <button class="btn btn-primary" type="submit">
                              <span class="glyphicon glyphicon-search"></span>
                            </button>
                          </div>
                        </div> -->
                      </div>
                    </div>
                  </div>
                </form>
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
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone No.</th>
                        <th>Designation</th>
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
                            <td>{{ucwords($value->name)}}</td>
                            <td>{{$value->email}}</td>
                            <td>{{$value->mobile}}</td>
                            <td>{{ucwords($value->designation)}}</td>
                            
                            <td>
                              <a href="{{ url('/admin/editContact/')}}/{{$dealer_id}}/{{$value->id}}" class="btn btn-success">Edit</a>
                              <a href="{{ url('/admin/statusContact/delete/')}}/{{$dealer_id}}/{{$value->id}}" onclick="return confirm('Are you sure want to delete?')" class="btn btn-danger">Delete</a>
                            </td>
                          </tr>   
                      <?php
                        $i++;
                        }
                       }else{?>
                        <tr>
                          <td colspan="5">No Record</td>                          
                        </tr>
                      <?php }?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr. No.</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone No.</th>
                        <th>Designation</th>
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