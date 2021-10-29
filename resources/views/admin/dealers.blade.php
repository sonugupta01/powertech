@extends('layouts.dashboard')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Dealer/Office Management
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Dealer/Office Management</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Dealers/Office List</h3>
            <a href="{{url('/admin/addDealer')}}" class="btn btn-success floatright" style="heigh
            34px;"><i class="fa fa-plus" aria-hidden="true" style="padding: 3px;"></i></a>
            <form method="GET" action="{{asset('/admin/downloadDealers')}}">
              <input type="hidden" name="search" value="{{@$search}}">
              <input type="hidden" name="firm_id" value="{{@$firm_id}}">
              <input type="hidden" name="asm_id" value="{{@$asm_id}}">
              <input type="hidden" name="status" value="{{@$status}}">
              <input type="submit" class="btn btn-success floatright btn-div" name="download" id="download" value="Download" style="margin-top: -17px; margin-right: 10px;">
            </form>
          </div><!-- /.box-header -->
          <form action="" method="GET" id="filterform"> 
            <div class="row">
              <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="col-xs-12 col-sm-6 col-md-2">
                  <div class="input-group from-group pull-right">
                    <select class="form-control" id="status" name="status">
                      <option value="">Status</option>
                      <option value="activated" {{$status=='activated'?'selected':''}}>Activated</option>
                      <option value="deactivated" {{$status=='deactivated'? 'selected':''}}>Deactivated</option>
                    </select>
                  </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-2">
                  <div class="input-group from-group pull-right">
                    <select class="form-control" id="firm_id" name="firm_id">
                      <option value="">Select Firm</option>
                      @foreach($firms as $firm)
                        <option value="{{$firm->id}}" @if($firm_id == $firm->id) {{ 'selected' }} @endif>{{get_firm_short_code($firm->id)}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-2">
                  <div class="input-group from-group pull-right">
                    <select class="form-control" id="asm_id" name="asm_id">
                      <option value="">Select ASM</option>
                      @foreach($asms as $asm)
                        <option value="{{$asm->id}}" @if($asm_id == $asm->id) {{ 'selected' }} @endif>{{$asm->name}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-2">
                  <div class="input-group from-group pull-right">
                    <select class="form-control" id="type" name="type">
                      <option value="">Select Type</option>
                      <option value="dealer" {{$type=='dealer'?'selected':''}}>Dealer</option>
                      <option value="office" {{$type=='office'? 'selected':''}}>Office</option>
                    </select>
                  </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4">
                  <div class="input-group from-group">
                    <input type="text" class="form-control" name="search" placeholder="Search by name, email or mobile no." id="txtSearch" value="{{ old('search', $search) }}">
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
          <div class="row">
            <div class="col-xs-12 col-sm-6 col-md-6 pull-left">
              <b style="margin-top: 10px; font-size: 12px; display: block;">Total Records: {{$result->total()}}</b>
            </div>
              <div class="col-xs-12 col-sm-6 col-md-6 pull-right">
                <a href="{{asset('admin/dealer_management')}}" class="btn btn-success" name="refresh" id="refresh" type="button" style="height: 34px;"><i class="fa fa-refresh" aria-hidden="true" style="line-height: inherit;"></i></a>
              </div>
          </div>
          <div class="box-body " style="overflow-x:auto;">
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
                    <th>Center Code</th>
                    <th>Name</th>
                    {{-- <th>Email</th>
                    <th>Mobile No.</th> --}}
                    <th>Active Advisors</th>
                     <th>Status</th> 
                    <th>Action</th>
                    <th>View</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if(count($result)>=1){
                    $i=1;
                    foreach($result as $value) { 
                  ?>
                      <tr>
                        <td>{{$value->center_code}}</td>
                        <td>{{ucwords(strtolower($value->name))}}</td>
                        {{-- <td class="email-id">{{get_emails($value->id)}}</td>
                        <td>{{$value->mobile_no}}</td> --}}
                        <td style="text-align: center;">{{get_advisors($value->id)}}</td>
                        <td>@if($value->status == "1") Activated @else Deactivated @endif</td> 
                        <td>
                            @if($value->status == "1")
                              <a href="{{ url('/admin/statusDealer/deactivate/')}}/{{$value->id}}"  onclick="return confirm('Are you sure want to deactivate?')" class="btn btn-danger">Deactivate</a> 
                            @else 
                              <a href="{{ url('/admin/statusDealer/activate/')}}/{{$value->id}}" onclick="return confirm('Are you sure want to activate?')" class="btn btn-info" style="width: 92.15px">Activate</a>
                            @endif 
                              <a href="{{ url('/admin/editDealer/')}}/{{$value->id}}" class="btn btn-success">Edit</a>
                               <a href="{{ asset('images')}}/{{ $value->qrcode}}" class="btn btn-primary" download="">QR Code</a>
                               <a href="{{ url('/admin/dealerProducts')}}/{{$value->id}}" class="btn btn-warning">MIL</a>
                              {{-- <a href="{{ url('/admin/statusDealer/delete/')}}/{{$value->id}}" onclick="return confirm('Are you sure want to delete?')" class="btn btn-danger">Delete</a> --}}
                        </td>
                        <td>
                          <a href="{{ url('/admin/contacts/')}}/{{$value->id}}" class="btn btn-primary">Contacts</a>
                          @if($value->type == 'dealer')
                          <a href="{{ url('/admin/advisors/')}}/{{$value->id}}" class="btn btn-info">Service Advisors</a>
                          <a href="{{ url('/admin/dealerTemplates/')}}/{{$value->id}}" class="btn btn-warning">Templates</a>
                          <a href="{{ url('/admin/dealers_SalesExecutivesListing/')}}/{{$value->id}}" class="btn btn-success" title="Sales Executives">SE's</a>
                          <a href="{{ url('/admin/dealers_ASM/')}}/{{$value->id}}" class="btn btn-default" title="Dealer's ASM">ASM / RSM / SSE</a>
                          <a href="{{ url('/admin/dealer_percentage_history/')}}/{{$value->id}}" class="btn btn-danger" title="Percentage History"><i class="fa fa-percent"></i> History</a>
                          @endif
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
                    <th>Center Code</th>
                    <th>Name</th>
                    {{-- <th>Email</th>
                    <th>Mobile No.</th> --}}
                    <th>Active Advisors</th>
                    <th>Status</th> 
                    <th>Action</th>
                    <th>View</th>
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
<script>
setTimeout(function() {
  $('.alert').fadeOut('slow');
}, 3000);
$('#type').on('change',function(){
  $( "#filterform" ).submit();
});
$('#firm_id').on('change',function(){
  $( "#filterform" ).submit();
});
$('#status').on('change',function(){
  $( "#filterform" ).submit();
});
$('#asm_id').on('change',function(){
  $( "#filterform" ).submit();
});
</script>
@endsection