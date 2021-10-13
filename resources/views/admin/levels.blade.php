@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Designation
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/designation')}}"><i class="fa fa-dashboard"></i> Designations</a></li>
      <li class="active">Level</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Level List</h3>
            <a href="{{url('/admin/addLevel')}}" class="btn btn-success floatright">Add Level</a>
          </div><!-- /.box-header -->
          <form action="" method="GET"> 
            <div class="row">
              <div class="col-xs-12 col-md-12">
                <div class="col-xs-6 col-md-6 pull-left">
                </div>
                      <!-- <div class="col-xs-4 col-md-4 pull-right">
                        <div class="input-group ">
                          <input type="text" class="form-control" name="search" placeholder="Search by name, email or mobile no." id="txtSearch">
                          <div class="input-group-btn">
                            <button class="btn btn-primary" type="submit">
                              <span class="glyphicon glyphicon-search"></span>
                            </button>
                          </div>
                        </div>
                      </div> -->
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
                        <th>Level</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($levels)>=1){
                        $i=1;
                        foreach($levels as $value) { 
                          ?>
                          <tr>
                            <td>{{ucwords($value->level)}}</td>
                            <td>
                              <a href="{{ url('/admin/editLevel/')}}/{{$value->id}}" class="btn btn-success">Edit</a>
                            </td>
                          </tr>   
                          <?php
                          $i++;
                        }
                      }
                      else{?>

                      <tr>
                        <td colspan="2">No Record</td>                          
                      </tr>
                      <?php }?> 
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>level</th>
                        <th>Action</th>
                      </tr>
                    </tfoot>
                  </table>
                </div><!-- /.box-body -->
                <?php echo $levels->links(); ?>
              </div><!-- /.box -->
            </div><!-- /.col -->
          </div><!-- /.row -->
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
<script>
setTimeout(function() {
  $('.alert').fadeOut('slow');
}, 2000);
</script>   
@endsection