@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Treatment
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Treatment</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Treatments List</h3>
            <button id="download" class="btn btn-primary floatright" style="margin-left: 5px;">Download</button>
            <a href="{{ url('/admin/uploadTreatment')}}" style="margin-left: 5px;" class="btn btn-warning floatright ">Upload</a>
            <a href="{{url('/admin/addTreatment')}}" class="btn btn-success floatright btn-div">Add Treatment</a>
          </div><!-- /.box-header -->
          <form action="" id="form1" method="GET"> 
            <div class="row">
              <div class="col-xs-12 col-md-12">
                <div class="col-xs-12 col-sm-6 col-md-5 pull-left">
                  <div class="input-group form-group ">
                    <input class="form-control" type="text" placeholder="Search by treatment or type" value="{{@$oldSearch}}" name="search">
                    <div class="input-group-btn">
                      <button class="btn btn-primary" type="submit" style="height: 34px;">
                        <span class="glyphicon glyphicon-search"></span>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-1 pull-right">
                  <a href="{{asset('admin/treatments')}}" class="btn btn-success" type="button" style="height: 34px;"><i class="fa fa-refresh" aria-hidden="true" style="line-height: inherit;"></i></a>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-2 pull-right">
                  <div class="input-group form-group pull-right">
                    <select class="form-control" id="treatment_option" name="treatment_option">
                      <option style="" value="">Select Option</option>
                      <option value="1" {{($oldOption=='1')?'selected':''}}>Recheck</option>
                      <option value="2" {{($oldOption=='2')?'selected':''}}>Repeat</option>
                    </select>
                  </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-2 pull-right">
                  <div class="input-group form-group pull-right">
                    <select class="form-control" id="model" name="model">
                      <option style="width: 350px;" value="">Select Model</option>
                      @foreach($models as $value)
                        <option {{($oldModel==$value->id)?'selected':''}} value="{{$value->id}}">{{$value->model_name}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-2 pull-right">
                  <div class="input-group form-group pull-right">
                    <select class="form-control" id="template" name="template">
                      <option style="width: 350px;" value="">Select Template</option>
                      @foreach($templates as $template)
                        <option {{($oldTemplate==$template->id)?'selected':''}} value="{{$template->id}}">{{$template->temp_name}}</option>
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
            <table id="exa" class="table table-bordered table-striped text-center">
              <thead>
                <tr>
                  <th>Treatment</th>
                  <th>Template</th>
                  <th>Size- Model</th>
                  <th>Type of Treatment</th>
                  <th>Labour Code</th>
                  <th>Customer Price</th>
                  <th>Dealer Price</th>
                  <th>Incentive</th>
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
                      <td>{{ucwords($value->treatment)}}</td>
                      <td>{{get_template_name(ucwords($value->temp_id))}}</td>
                      <td>
                        <?php 
                          if($value->model_size==1){
                            echo 'Large- '.$value->model_name;
                          }elseif($value->model_size==2){
                            echo 'Medium- '.$value->model_name;
                          }elseif($value->model_size==3){
                            echo 'Small- '.$value->model_name;
                          }
                        ?>
                      </td>
                      <!-- <td style="width: 50px;">{{ucwords($value->model_name)}}</td> -->
                      <td>@if($value->treatment_type==0) Normal @elseif($value->treatment_type==1) Heavy @endif</td>
                      <td>{{$value->labour_code}}</td>
                      <td>{{round($value->customer_price)}}</td>
                      <td>{{round($value->dealer_price)}}</td>
                      <td>{{round($value->incentive)}}</td>
                      <td>@if($value->status==1) Activate @else Deactivate @endif</td>
                      <td>
                            @if($value->status == "1")
                              <a href="{{ url('/admin/statusTreatment/deactivate/')}}/{{$value->id}}"  onclick="return confirm('Are you sure want to deactivate?')" class="btn btn-warning">Deactivate</a> 
                            @else 
                              <a href="{{ url('/admin/statusTreatment/activate/')}}/{{$value->id}}" onclick="return confirm('Are you sure want to activate?')" class="btn btn-info" style="width: 92.16px">Activate</a>
                            @endif
                            <a href="{{ url('/admin/editTreatment/')}}/{{$value->id}}" class="btn btn-success"><i class="fa fa-edit"></i></a>
                            <a href="{{ url('/admin/statusTreatment/delete/')}}/{{$value->id}}" class="btn btn-danger" onclick="return confirm('Are you sure want to delete?')"><i class="fa fa-trash"></i></a>
                            <!-- <a href="{{ url('/admin/treatmentProducts/')}}/{{$value->id}}" class="btn btn-primary">Product</a> -->
                      </td>
                    </tr>   
                <?php
                  $i++;
                  }
                 }else{?>
                  <tr>
                    <td colspan="10">No Record</td>                          
                  </tr>
                <?php }?>
              </tbody>
              <tfoot>
                <tr>
                  <th>Treatment</th>
                  <th>Template</th>
                  <th>Size- Model</th>
                  <!-- <th>Model</th>
                  <th>Dealer</th> -->
                  <th>Type of Treatment</th>
                  <th>Labour Code</th>
                  <th>Customer Price</th>
                  <th>Dealer Price</th>
                  <th>Incentive</th>
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
<form method="get" id="form12" action="{{url('/admin/downloadTreatment')}}">
  <input type="hidden" name="search" value="{{@$oldSearch}}">
  <input type="hidden" name="model" value="{{@$oldModel}}">
  <input type="hidden" name="template" value="{{@$oldTemplate}}">
  <input type="hidden" name="dealer" value="{{@$oldDealer}}">
  <input type="hidden" name="treatment_option" value="{{@$oldOption}}">
</form>  
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

   $('#template').on('change',function(){
      $( "#form1" ).submit();
   });

   $('#treatment_option').on('change',function(){
      $( "#form1" ).submit();
   });

  $(document).on('click','#download',function(){
    $('#form12').submit();
  });

  });
</script>  
@endsection