@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Treatment Template
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Treatment Template</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Treatment Template List</h3>
            <a href="{{url('/admin/addTreatmentTemplate')}}" class="btn btn-success floatright">Add Treatment Template</a>
          </div><!-- /.box-header -->
          <form action="" method="GET"> 
            <div class="row">
              <div class="col-xs-12 col-md-12">
                <div class="col-xs-6 col-md-6 pull-left">
                </div>
                <div class="col-xs-4 col-md-4 pull-right">
                  <div class="input-group ">
                    <input type="text" class="form-control" name="search" placeholder="Search by name" id="txtSearch">
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
                  <th>OEM</th>
                  <th>Template Name</th>
                  <th>Template Description</th>
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
                      <td>{{get_oem_name($value->oem_id)}}</td>
                      <td>{{ucwords($value->temp_name)}}</td>
                      <td>{{$value->temp_description}}</td>
                      <td>
                        <a href="{{ url('/admin/editTreatmentTemplate')}}/{{$value->id}}" class="btn btn-success">Edit</a>
                        <a href="{{ url('/admin/statusTreatTemp/delete')}}/{{$value->id}}" onclick="return confirm('Are you sure want to delete?')" class="btn btn-danger">Delete</a>
                        <a href="{{ url('/admin/addDuplicateTemplate')}}/{{$value->id}}" class="btn btn-warning" title="Duplicate"><i class="fa fa-clone"></i></a>
                        <a href="{{ url('/admin/addPercentagePrice')}}/{{$value->id}}" class="btn btn-primary" title="update Treatments Price"><i class="fa fa-percent" aria-hidden="true"></i></a>
                        <a id="template{{$value->id}}" class="btn btn-info"><i class="fa fa-eye" aria-hidden="true"></i></a>
                        <form action="{{url('/admin/treatments')}}" id="form{{$value->id}}" method="GET">
                          <input type="hidden" name="template" value="{{$value->id}}">
                        </form>
                      </td>
                    </tr>
                    <script>
                        $(document).ready(function(){
                          $('#template<?php echo $value->id; ?>').on('click',function(){
                            $( "#form<?php echo $value->id; ?>" ).submit();
                          });
                        });
                    </script>   
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
                  <th>OEM</th>
                  <th>Template Name</th>
                  <th>Template Description</th>
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