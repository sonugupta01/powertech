@extends('layouts.dashboard')

@section('content')
<?php 
  $url = $result->currentPage();
  session()->put('page', $url);
?>
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      ASM
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">ASM</li>
    </ol>
  </section>

        <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">ASM List</h3>
            <!-- <button id="download" class="btn btn-primary floatright" style="margin-left: 5px;">Download</button>
            <a href="{{ url('/admin/uploadASM')}}" style="margin-left: 5px;" class="btn btn-warning floatright ">Upload</a> -->
            <a href="{{url('/admin/addASM')}}" class="btn btn-success floatright btn-div">Add ASM</a>
          </div><!-- /.box-header -->
          
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
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile No.</th>
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
                    <td>{{ucwords($value->name)}}</td>
                    <td>{{$value->email}}</td>
                    <td>{{$value->mobile_no}}</td>
                    <td>@if($value->status == "1") Activate @else Deactivate @endif</td>
                    <td>
                      @if($value->status == "1")
                        <a href="{{ url('/admin/statusASM/deactivate/')}}/{{$value->id}}"  onclick="return confirm('Are you sure want to deactivate?')" class="btn btn-warning">Deactivate</a> 
                      @else
                        <?php 
                          $num = $value->mobile_no;
                          $res = substr($num, 0, 3);
                        ?>
                        @if($res=="dup")
                        <a href="{{ url('/admin/statusASM/activate/')}}/{{$value->id}}" onclick="return confirm('Are you sure want to activate?')" class="btn btn-info" style="width: 92.15px" disabled>Activate</a>
                        @else
                         <a href="{{ url('/admin/statusASM/activate/')}}/{{$value->id}}" onclick="return confirm('Are you sure want to activate?')" class="btn btn-info" style="width: 92.15px">Activate</a> 
                        @endif
                      @endif
                      <a href="{{ url('/admin/editASM/')}}/{{$value->id}}" class="btn btn-success">Edit</a>
                      <a href="{{ url('/admin/statusASM/delete/')}}/{{$value->id}}" class="btn btn-danger" onclick="return confirm('Are you sure want to delete?')">Delete</a>
                      <a href="{{ url('/admin/asm_SalesExecutiveListing/')}}/{{$value->id}}" class="btn btn-primary">Sales Executives</a>
                    </td>
                  </tr>   
                  <?php
                    $i++;
                    }
                   }else{?>
                    <tr>
                      <td colspan="6">No Record</td>                          
                    </tr>
                  <?php }?>   
                </tbody>
                <tfoot>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile No.</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </tfoot>
              </table>
            </div>
            <div class="pull-left">
              {!! $result->render() !!}
            </div>
            
          </div><!-- /.box-body -->
        </div><!-- /.box -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->
 
<script type="text/javascript">
setTimeout(function() {
  $('.alert').fadeOut('slow');
}, 3000);

  $(document).ready(function(){

   $('#dealer').on('change',function(){
      $( "#form1" ).submit();
   });

   $('#model').on('change',function(){
      $( "#form1" ).submit();
   });

  $(document).on('click','#download',function(){
    $('#form12').submit();
  });

  });
</script>
@endsection