@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Products
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Products</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Products List</h3>
            <a href="{{url('/admin/addProduct')}}" class="btn btn-success floatright">Add Product</a>
          </div><!-- /.box-header -->
          <form action="" method="GET" id="product_form"> 
            <div class="row">
              <div class="col-xs-12 col-md-12">
                <div class="col-xs-12 col-sm-6 col-md-5 pull-left">
                  <b style="margin-top: 10px; font-size: 12px; display: block;">Total Records: {{$result->total()}}</b>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-2">
                  <select class="form-control required" id="brand_id" name="brand_id">
                    <option value="">Select Brand</option>
                    @foreach($brands as $brand)
                    <option value="{{$brand->id}}" @if($brand_id  == $brand->id) {{ 'selected' }} @endif>{{ucwords($brand->brand_name)}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-5 pull-right">
                  <div class="col-xs-12 col-sm-6 col-md-10">
                    <div class="input-group from-group">
                      <input type="text" class="form-control" name="search" placeholder="Search by name" id="txtSearch" value="{{$search}}">
                      <div class="input-group-btn">
                        <button class="btn btn-primary" type="submit" style="height: 34px;">
                          <span class="glyphicon glyphicon-search"></span>
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="col-xs-12 col-sm-6 col-md-2">
                    <a href="{{ url('/admin/products')}}" class="btn btn-primary pull-right" style="height: 34px; text-align: center;"><i class="fa fa-refresh" aria-hidden="true" style="padding: 3px;"></i></a>
                  </div>
                </div>
              </div>
            </div>
          </form>
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
                    <th>Name</th>
                    <th>Quantity(Unit of Measurement)</th>
                    <th>Price</th>
                    <th>Brand</th>
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
                        <td>{{$value->quantity}}@if($value->uom==1){{'Litre'}}@elseif($value->uom==2){{'ML'}}@elseif($value->uom==3){{'Pcs.'}}@elseif($value->uom==4){{'Gms.'}}@endif</td>
                        <td>{{$value->price}}</td>
                        <td>{{getBrandName($value->brand_id)}}</td>
                        <td>@if($value->status == "1") Activate @else Deactivate @endif</td> 
                        <td>
                            @if($value->status == "1")
                              <a href="{{ url('/admin/statusProduct/deactivate/')}}/{{$value->id}}"  onclick="return confirm('Are you sure want to deactivate?')" class="btn btn-danger">Deactivate</a> 
                            @else 
                              <a href="{{ url('/admin/statusProduct/activate/')}}/{{$value->id}}" onclick="return confirm('Are you sure want to activate?')" class="btn btn-info" style="width: 92.16px">Activate</a>
                            @endif 
                              <a href="{{ url('/admin/editProduct/')}}/{{$value->id}}" class="btn btn-success">Edit</a>
                              <!--<a href="{{ url('/admin/statusProduct/delete/')}}/{{$value->id}}" onclick="return confirm('Are you sure want to delete?')" class="btn btn-danger">Delete</a>-->
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
                    <th>Name</th>
                    <th>Quantity(Unit of Measurement)</th>
                    <th>Price</th>
                    <th>Brand</th>
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
<script>
setTimeout(function() {
  $('.alert').fadeOut('slow');
}, 3000);

$(document).ready(function(){
  $("#brand_id").change(function(){
    $('#product_form').submit();
  });
});
</script>    
@endsection