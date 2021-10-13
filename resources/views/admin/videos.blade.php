@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Videos
            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('/admin/gallery')}}"><i class="fa fa-folder-open"></i> Gallery</a></li>
            <li class="active">Videos</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Videos List</h3>
                  <a href="{{url('/admin/addVideo')}}" class="btn btn-success floatright">Add Video</a>
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
                  <div class="table-responsive">                 
                    <table id="exa" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Title</th>
                        <th>Video</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($result)>=1){
                        $i=1;
                        foreach($result as $value) { 
                      ?>
                          <tr>
                            <td>{{ucwords($value->title)}}</td>
                            <td>
                              <iframe width="200" height="100" src="{{getYoutubeEmbedUrl($value->path)}}" frameborder="0"  allowfullscreen></iframe>
                            </td>
                            <td>@if($value->status==1){{'Activate'}} @else {{'Deactivate'}} @endif</td>
                            <td>
                              @if($value->status == "1")
                                  <a href="{{ url('/admin/statusVideo/deactivate/')}}/{{$value->id}}"  onclick="return confirm('Are you sure want to deactivate?')" class="btn btn-warning">Deactivate</a> 
                                @else 
                                  <a href="{{ url('/admin/statusVideo/activate/')}}/{{$value->id}}" onclick="return confirm('Are you sure want to activate?')" class="btn btn-info">Activate</a>
                                @endif
                              <a href="{{ url('/admin/editVideo/')}}/{{$value->id}}" class="btn btn-success">Edit</a>
                              <a href="{{ url('/admin/statusVideo/delete/')}}/{{$value->id}}" onclick="return confirm('Are you sure want to delete?')" class="btn btn-danger">Delete</a>
                            </td>
                          </tr>   
                      <?php
                        $i++;
                        }
                       }else{?>
                        <tr>
                          <td colspan="4">No Record</td>                          
                        </tr>
                      <?php }?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Title</th>
                        <th>Video</th>
                        <th>Status</th>
                        <th>Action</th>
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
@endsection