@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            History Jobs
            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">History Jobs</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">History Jobs List</h3>
                  <a href="{{url('/admin/uploadJobHistory')}}" class="btn btn-success floatright">Upload Job History</a>
                </div><!-- /.box-header -->
                <form action="" method="GET" id="form1"> 
                  <div class="row">
                    <div class="col-xs-12 col-md-12">
                      <div class="col-xs-12 col-sm-6 col-md-7 pull-left">
                      	<div class="col-xs-7 col-sm-6 col-md-5">
	                        <div class="input-group from-group">
	                          <input type="text" class="form-control" name="regn_no" placeholder="Search by Regn No.">
	                        </div>
	                    </div>
                      <div class="col-xs-5 col-sm-6 col-md-5">
                        <div class="input-group from-group">
                          	<input type="submit" class="btn btn-primary btn-div" value="Search">
                        </div>
                        </div>
                      </div>
                      <div class="col-xs-12 col-sm-6 col-md-4 pull-right">
                        <div class="input-group ">
                          <select class="form-control" id="supervisor" name="search">
                            <option style="width: 350px;" value="">Select Dealer</option>
                            @foreach($dealers as $value)
                              <option {{($oldSupervisor==$value->id)?'selected':''}} value="{{$value->id}}">{{ucwords($value->name)}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
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
                        <th>Job Date</th>
                        <th>Job Card No.</th>
                        <th>Bill No.</th>
                        <th>Regn No.</th>
                        <th>Model</th>
                        <th>Advisor</th>
                        <th>Treatment</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($result)>=1){
                         foreach($result as $value) { 
                      ?>
                          <tr>
                            <td>{{date('d-M-Y',strtotime($value->job_date))}}</td>
                            <td>{{$value->job_card}}</td>
                            <td>{{$value->bill_no}}</td>
                            <td>{{$value->regn_no}}</td>
                            <td>{{$value->model}}</td>
                            <td>{{$value->advisor}}</td>
                            <td>{{$value->treatment}}</td>
                          </tr>   
                      <?php
                        }
                       }else{?>
                        <tr>
                          <td colspan="7">No Record</td>                          
                        </tr>
                      <?php }?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <tr>
                        <th>Job Date</th>
                        <th>Job Card No.</th>
                        <th>Bill No.</th>
                        <th>Regn No.</th>
                        <th>Model</th>
                        <th>Advisor</th>
                        <th>Treatment</th>
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
<form id="deleteForm" action="{{url('/admin/deleteJobs')}}" method="GET">
  <input type="hidden" class="" name="selectedId" value="">
</form> 
<script type="text/javascript">
  $(document).ready(function(){

   $('#supervisor').on('change',function(){
      $( "#form1" ).submit();
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